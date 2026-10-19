<?php
declare(strict_types=1);

namespace WooWebhookResilience;

defined('ABSPATH') || exit;

final class Plugin {
    public const VERSION = '1.0.0';
    public const OPTION_KEY = 'wwr_settings';

    private static ?self $instance = null;
    private Dispatcher $dispatcher;
    private DeadLetterQueue $dlq;
    private Admin $admin;

    public static function instance(): self {
        return self::$instance ??= new self();
    }

    private function __construct() {
        $this->dispatcher = new Dispatcher();
        $this->dlq        = new DeadLetterQueue();
        $this->admin      = new Admin();
    }

    public function init(): void {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'render_woocommerce_missing_notice']);
            return;
        }

        $this->dispatcher->register();
        $this->dlq->register();
        $this->admin->register();
    }

    public function render_woocommerce_missing_notice(): void {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('WooCommerce Resilient Webhook requires WooCommerce to be installed and active.', 'woo-webhook-resilience'); ?></p>
        </div>
        <?php
    }

    public static function get_plugin_dir(): string {
        return plugin_dir_path(dirname(__FILE__));
    }

    public static function get_plugin_url(): string {
        return plugin_dir_url(dirname(__FILE__));
    }
}

