<?php
declare(strict_types=1);

namespace WooWebhookResilience;

defined('ABSPATH') || exit;

final class DeadLetterQueue {
    public const DLQ_OPTION = 'wwr_dead_letter_queue_log';
    public const MAX_STORED = 100;

    public function register(): void {
        add_action('wp_ajax_wwr_replay_webhook', [$this, 'handle_ajax_replay']);
    }

    public static function record_incident(array $entry): void {
        $log = get_option(self::DLQ_OPTION, []);
        if (!is_array($log)) {
            $log = [];
        }

        array_unshift($log, $entry);
        if (count($log) > self::MAX_STORED) {
            $log = array_slice($log, 0, self::MAX_STORED);
        }

        update_option(self::DLQ_OPTION, $log, false);
    }

    public static function get_all(): array {
        $log = get_option(self::DLQ_OPTION, []);
        return is_array($log) ? $log : [];
    }

    public static function get_entry(string $event_id): ?array {
        $log = self::get_all();
        foreach ($log as $item) {
            if (($item['id'] ?? '') === $event_id) {
                return $item;
            }
        }
        return null;
    }

    public static function update_attempt(string $event_id, int $next_attempt, string $err, string $next_retry): void {
        $log = self::get_all();
        foreach ($log as &$item) {
            if (($item['id'] ?? '') === $event_id) {
                $item['attempt']    = $next_attempt;
                $item['last_error'] = $err;
                $item['next_retry'] = $next_retry;
                break;
            }
        }
        update_option(self::DLQ_OPTION, $log, false);
    }

    public static function update_status(string $event_id, string $status, string $message): void {
        $log = self::get_all();
        foreach ($log as &$item) {
            if (($item['id'] ?? '') === $event_id) {
                $item['status']     = $status;
                $item['last_error'] = $message;
                break;
            }
        }
        update_option(self::DLQ_OPTION, $log, false);
    }

    public function handle_ajax_replay(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Permission denied.', 'woo-webhook-resilience')]);
        }
        check_ajax_referer('wwr_replay_nonce', 'nonce');

        $event_id = isset($_POST['event_id']) ? sanitize_key((string) $_POST['event_id']) : '';
        $entry    = self::get_entry($event_id);

        if (!$entry) {
            wp_send_json_error(['message' => __('DLQ item not found.', 'woo-webhook-resilience')]);
        }

        $webhook_id = (int) ($entry['webhook_id'] ?? 0);
        $dispatcher = new Dispatcher();
        $dispatcher->execute_retry_attempt($event_id, $webhook_id, (int) ($entry['attempt'] ?? 1));

        wp_send_json_success(['message' => __('Replay executed successfully!', 'woo-webhook-resilience')]);
    }
}

