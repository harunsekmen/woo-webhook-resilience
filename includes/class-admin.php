<?php
declare(strict_types=1);

namespace WooWebhookResilience;

defined('ABSPATH') || exit;

final class Admin {
    public function register(): void {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function add_menu_page(): void {
        add_submenu_page(
            'woocommerce',
            __('Webhook Dead-Letter Queue', 'woo-webhook-resilience'),
            __('Webhook DLQ', 'woo-webhook-resilience'),
            'manage_woocommerce',
            'wwr-dlq-console',
            [$this, 'render_dashboard']
        );
    }

    public function enqueue_assets(string $hook): void {
        if (strpos($hook, 'wwr-dlq-console') === false) {
            return;
        }

        $plugin_url = Plugin::get_plugin_url();

        wp_enqueue_style('wwr-admin-css', $plugin_url . 'assets/css/dlq-console.css', [], Plugin::VERSION);
        wp_enqueue_script('wwr-admin-js', $plugin_url . 'assets/js/dlq-console.js', ['jquery'], Plugin::VERSION, true);

        wp_localize_script('wwr-admin-js', 'wwrData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('wwr_replay_nonce'),
            'i18n'    => [
                'replaying' => __('Replaying delivery...', 'woo-webhook-resilience'),
                'success'   => __('Replayed successfully!', 'woo-webhook-resilience'),
            ],
        ]);
    }

    public function render_dashboard(): void {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $queue = DeadLetterQueue::get_all();
        require_once Plugin::get_plugin_dir() . 'templates/admin-dashboard.php';
    }
}

