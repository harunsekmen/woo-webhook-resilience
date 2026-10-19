<?php
declare(strict_types=1);

namespace WooWebhookResilience;

defined('ABSPATH') || exit;

/**
 * @var array $queue
 */
?>
<div class="wrap wwr-admin-wrap">
    <h1><?php esc_html_e('WooCommerce Resilient Webhook Dead-Letter Dispatcher', 'woo-webhook-resilience'); ?></h1>
    <p class="description">
        <?php esc_html_e('Guaranteed webhook delivery engine with exponential backoff (1m, 5m, 15m, 1h, 6h). Inspect failed third-party payloads and trigger 1-click manual replays.', 'woo-webhook-resilience'); ?>
    </p>

    <div class="wwr-grid" style="display: grid; grid-template-columns: 3fr 1fr; gap: 24px; margin-top: 20px;">
        <div class="wwr-main-col">
            <div class="postbox" style="padding: 24px;">
                <h2 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Dead-Letter Queue (DLQ) & Active Retries', 'woo-webhook-resilience'); ?></h2>

                <?php if (empty($queue)): ?>
                    <p style="color: #64748b; font-style: italic;"><?php esc_html_e('Queue is clear! All outbound webhooks to HubSpot, Klaviyo, and shipping APIs are delivering with 2xx HTTP success.', 'woo-webhook-resilience'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Event / Topic', 'woo-webhook-resilience'); ?></th>
                                <th><?php esc_html_e('Target Endpoint', 'woo-webhook-resilience'); ?></th>
                                <th><?php esc_html_e('Attempt', 'woo-webhook-resilience'); ?></th>
                                <th><?php esc_html_e('Status', 'woo-webhook-resilience'); ?></th>
                                <th><?php esc_html_e('Last Error', 'woo-webhook-resilience'); ?></th>
                                <th><?php esc_html_e('Actions', 'woo-webhook-resilience'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queue as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($item['topic'] ?? 'order.created'); ?></strong><br>
                                        <span style="font-size: 11px; color: #64748b;"><?php echo esc_html($item['id'] ?? ''); ?></span>
                                    </td>
                                    <td><code style="font-size: 11px;"><?php echo esc_html($item['target_url'] ?? ''); ?></code></td>
                                    <td><?php echo esc_html((string) ($item['attempt'] ?? 1)); ?> / <?php echo esc_html((string) ($item['max_attempts'] ?? 5)); ?></td>
                                    <td>
                                        <span class="wwr-status-badge wwr-status-<?php echo esc_attr($item['status'] ?? 'retrying'); ?>" style="padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 11px; background: <?php echo ($item['status'] ?? '') === 'succeeded' ? '#dcfce7; color: #166534;' : (($item['status'] ?? '') === 'dead_letter' ? '#fee2e2; color: #991b1b;' : '#fef3c7; color: #92400e;'); ?>">
                                            <?php echo esc_html(strtoupper($item['status'] ?? 'retrying')); ?>
                                        </span>
                                    </td>
                                    <td><span style="color: #dc2626; font-size: 12px;"><?php echo esc_html($item['last_error'] ?? ''); ?></span></td>
                                    <td>
                                        <button type="button" class="button button-small wwr-replay-btn" data-event-id="<?php echo esc_attr($item['id'] ?? ''); ?>">
                                            <?php esc_html_e('↻ Replay', 'woo-webhook-resilience'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="wwr-side-col">
            <div class="postbox" style="padding: 24px;">
                <h2 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Resilience Policy', 'woo-webhook-resilience'); ?></h2>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 14px; margin-bottom: 16px;">
                    <strong style="display: block; font-size: 13px; color: #0f172a; margin-bottom: 4px;"><?php esc_html_e('Exponential Backoff Ladder:', 'woo-webhook-resilience'); ?></strong>
                    <ul style="margin: 0; padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                        <li><strong>Attempt 1:</strong> Immediate (Default WC)</li>
                        <li><strong>Attempt 2:</strong> +1 minute</li>
                        <li><strong>Attempt 3:</strong> +5 minutes</li>
                        <li><strong>Attempt 4:</strong> +15 minutes</li>
                        <li><strong>Attempt 5:</strong> +1 hour</li>
                        <li><strong>Final:</strong> Retained in Dead-Letter Queue (DLQ)</li>
                    </ul>
                </div>
                <p style="font-size: 12px; color: #64748b;">
                    <?php esc_html_e('Built on top of WooCommerce Action Scheduler for high-concurrency resilience without blocking PHP worker threads.', 'woo-webhook-resilience'); ?>
                </p>
            </div>
        </div>
    </div>
</div>

