<?php
declare(strict_types=1);

namespace WooWebhookResilience;

use WC_Webhook;

defined('ABSPATH') || exit;

final class Dispatcher {
    public const RETRY_HOOK = 'wwr_retry_webhook_delivery_action';
    public const MAX_ATTEMPTS = 5;

    // Exponential intervals in seconds: 1m, 5m, 15m, 1h, 6h
    public const BACKOFF_DELAYS = [60, 300, 900, 3600, 21600];

    public function register(): void {
        add_action('woocommerce_webhook_delivery', [$this, 'inspect_delivery_result'], 10, 5);
        add_action(self::RETRY_HOOK, [$this, 'execute_retry_attempt'], 10, 3);
    }

    public function inspect_delivery_result(mixed $http_args, mixed $response, float $duration, mixed $arg, int $webhook_id): void {
        $status_code = wp_remote_retrieve_response_code($response);
        $is_error    = is_wp_error($response) || $status_code < 200 || $status_code >= 300;

        if (!$is_error) {
            return;
        }

        $webhook = new WC_Webhook($webhook_id);
        $topic   = $webhook->get_topic();
        $target  = $webhook->get_delivery_url();

        $payload = is_array($http_args) && isset($http_args['body']) ? (string) $http_args['body'] : '';
        $err_msg = is_wp_error($response) ? $response->get_error_message() : 'HTTP ' . $status_code;

        $event_id = uniqid('dlq_', true);
        $entry = [
            'id'           => $event_id,
            'webhook_id'   => $webhook_id,
            'topic'        => $topic,
            'target_url'   => $target,
            'payload'      => $payload,
            'attempt'      => 1,
            'max_attempts' => self::MAX_ATTEMPTS,
            'last_error'   => $err_msg,
            'status'       => 'retrying',
            'created_at'   => current_time('mysql'),
            'next_retry'   => current_time('mysql', time() + self::BACKOFF_DELAYS[0]),
        ];

        DeadLetterQueue::record_incident($entry);

        // Schedule first exponential retry via Action Scheduler
        if (function_exists('as_schedule_single_action')) {
            as_schedule_single_action(
                time() + self::BACKOFF_DELAYS[0],
                self::RETRY_HOOK,
                ['event_id' => $event_id, 'webhook_id' => $webhook_id, 'attempt' => 2]
            );
        }
    }

    public function execute_retry_attempt(string $event_id, int $webhook_id, int $attempt): void {
        $entry = DeadLetterQueue::get_entry($event_id);
        if (!$entry || $entry['status'] === 'succeeded' || $entry['status'] === 'abandoned') {
            return;
        }

        $webhook = new WC_Webhook($webhook_id);
        if (!$webhook->get_id()) {
            return;
        }

        // Attempt delivery replay
        $payload = $entry['payload'] ?? '';
        $target  = $webhook->get_delivery_url();

        $response = wp_safe_remote_post($target, [
            'method'      => 'POST',
            'timeout'     => 15,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => [
                'Content-Type' => 'application/json',
                'X-WC-Webhook-Source' => home_url('/'),
                'X-WWR-Retry-Attempt' => (string) $attempt,
            ],
            'body'        => $payload,
        ]);

        $status_code = wp_remote_retrieve_response_code($response);
        $is_success  = !is_wp_error($response) && ($status_code >= 200 && $status_code < 300);

        if ($is_success) {
            DeadLetterQueue::update_status($event_id, 'succeeded', 'Delivered successfully on retry #' . $attempt);
            return;
        }

        $err_msg = is_wp_error($response) ? $response->get_error_message() : 'HTTP ' . $status_code;

        if ($attempt < self::MAX_ATTEMPTS) {
            $delay_idx = min($attempt - 1, count(self::BACKOFF_DELAYS) - 1);
            $delay_seconds = self::BACKOFF_DELAYS[$delay_idx];

            DeadLetterQueue::update_attempt($event_id, $attempt + 1, $err_msg, current_time('mysql', time() + $delay_seconds));

            if (function_exists('as_schedule_single_action')) {
                as_schedule_single_action(
                    time() + $delay_seconds,
                    self::RETRY_HOOK,
                    ['event_id' => $event_id, 'webhook_id' => $webhook_id, 'attempt' => $attempt + 1]
                );
            }
        } else {
            DeadLetterQueue::update_status($event_id, 'dead_letter', 'Exhausted maximum retry budget (' . self::MAX_ATTEMPTS . ' attempts). Moved to DLQ.');
        }
    }
}

