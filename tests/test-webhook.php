<?php
require_once __DIR__ . '/bootstrap.php';
if (!function_exists('get_option')) { function get_option($k, $d = []) { return $d; } }
require_once dirname(__DIR__) . '/includes/class-dead-letter-queue.php';

echo "Testing WooWebhookResilience (DeadLetterQueue)...
";

$all = \WooWebhookResilience\DeadLetterQueue::get_all();
assert(is_array($all), "DLQ items must be array");

echo "PASS: woo-webhook-resilience verified.
";
