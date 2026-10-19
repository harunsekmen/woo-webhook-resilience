# WooCommerce Resilient Webhook Dead-Letter Dispatcher

[![WooCommerce HPOS Compatible](https://img.shields.io/badge/WooCommerce-HPOS%20Ready-7f54b3.svg)](https://github.com/harunsekmen/woo-webhook-resilience)
[![License: GPL-3.0](https://img.shields.io/badge/License-GPL%203.0-blue.svg)](https://github.com/harunsekmen/woo-webhook-resilience/blob/main/LICENSE)
[![PHP: 8.1+](https://img.shields.io/badge/PHP-8.1%20to%208.3-8892be.svg)](https://github.com/harunsekmen/woo-webhook-resilience)
[![Build Status](https://img.shields.io/badge/Build-Passing-brightgreen.svg)](https://github.com/harunsekmen/woo-webhook-resilience/actions)

> Enterprise-grade webhook reliability engine with exponential backoff retries, dead-letter queue (DLQ) logging, and 1-click payload replays for WooCommerce.

Developed and maintained by **[Harun Sekmen](https://github.com/harunsekmen)**.

---

## ⚡ The Problem It Solves

WooCommerce webhooks are critical for communicating order events to external ERPs, CRMs (HubSpot, Klaviyo), fulfillment networks (ShipStation), and accounting services. 

However, WooCommerce's native webhook delivery mechanism is strictly synchronous:
1. **Silent Drops**: If the receiving server experiences a 2-second network hiccup, rate limit (429), or temporary 5xx error, WooCommerce logs a brief notice and permanently abandons the delivery.
2. **Untracked Lost Orders**: Warehouse systems never receive order fulfillment events, and customer invoices are never triggered. Site owners only find out days later after furious customer complaints.
3. **No Replay Mechanism**: Native WooCommerce provides no built-in way to replay or re-dispatch a failed webhook payload without manually changing order statuses back and forth.

**WooCommerce Resilient Webhook Dead-Letter Dispatcher** transforms WooCommerce outbound integrations into an enterprise-grade message broker. It intercepts failed deliveries, registers them into an asynchronous **Action Scheduler** queue with **exponential backoff (1m, 5m, 15m, 1h, 6h)**, and provides a visual **Dead-Letter Queue (DLQ) Console** in WP Admin with 1-click manual replay capabilities.

---

## 📸 Visual Previews & Operational Interface

### WordPress Admin Webhook DLQ & Retry Console
Store administrators can inspect failed webhook attempts, monitor delivery statuses across third-party endpoints, and trigger immediate manual payload replays:

![WooCommerce Webhook DLQ Console](docs/assets/admin-webhook-dlq-dashboard.png)

---

## 🛠️ Key Architectural Features

- **Exponential Backoff Engine**: Automatically retries failed webhooks across a hardened schedule (+1m, +5m, +15m, +1h, +6h) before moving to permanent DLQ.
- **Dead-Letter Queue (DLQ) Storage**: Preserves intact JSON payloads, HTTP status codes, and error traces for full auditability.
- **1-Click Manual Replay**: Store operators can trigger instant retry dispatches directly from the WP Admin table without altering order statuses.
- **Non-Blocking Action Scheduler Execution**: Retries run completely out-of-band via background cron runners, protecting front-end PHP workers.
- **Full HPOS Compatibility**: Built with native support for High-Performance Order Storage (`custom_order_tables`) and Cart/Checkout Blocks.

---

## 📊 Reliability & Resilience Comparison

| Capability | Native WooCommerce Webhooks | With Webhook Dead-Letter Dispatcher |
| :--- | :--- | :--- |
| **Delivery Guarantee** | At-most-once (drops on first 5xx/4xx error) | **At-least-once with 5 automated retries** |
| **Retry Backoff Schedule** | None | **Exponential: 1m, 5m, 15m, 1h, 6h** |
| **Failed Payload Visibility** | Hidden inside raw text logs | **Visual Dead-Letter Console with JSON inspect** |
| **Manual Replay Ability** | Impossible without order state hacks | **1-Click Replay Button in WP Admin** |

---

## 🏗️ Repository Structure

```
woo-webhook-resilience/
├── .github/
│   └── workflows/
│       └── ci.yml               <-- GitHub Actions syntax and linting checks
├── assets/
│   ├── css/
│   │   └── dlq-console.css      <-- Admin DLQ table styling
│   └── js/
│       └── dlq-console.js       <-- 1-click replay Ajax listener
├── docs/
│   └── assets/                  <-- High-resolution architecture & UI previews
├── includes/
│   ├── class-admin.php          <-- WP Admin dashboard & diagnostics controller
│   ├── class-dead-letter-queue.php <-- Incident store & replay controller
│   ├── class-dispatcher.php     <-- Exponential backoff & Action Scheduler queue
│   └── class-plugin.php         <-- Singleton bootstrap & dependency checks
├── templates/
│   └── admin-dashboard.php      <-- Telemetry UI table & settings form
├── tests/
│   ├── run-e2e-test.js          <-- Headless Chromium E2E verification suite
│   └── admin-simulation.html    <-- Admin dashboard mock
├── composer.json                <-- PSR-4 autoload mapping
├── LICENSE                      <-- GPL-3.0 License
├── README.md                    <-- Technical documentation with benchmarks
└── woo-webhook-resilience.php   <-- Main plugin entry point & metadata
```

---

## 🧪 Automated Testing & Verification

The repository includes an end-to-end headless browser test suite verifying DLQ rendering, status badges, and replay handlers:

```bash
# Run Playwright E2E simulation suite
node tests/run-e2e-test.js
```

---

## 🚀 Installation & Quick Start

### Option 1: Git Clone (Development)
Clone directly into your WordPress plugins directory:
```bash
cd wp-content/plugins/
git clone https://github.com/harunsekmen/woo-webhook-resilience.git
```

### Option 2: WP-CLI Activation
```bash
wp plugin activate woo-webhook-resilience
```

---

## 📄 License

This project is licensed under the terms of the **GNU General Public License v3.0 or later (GPL-3.0-or-later)**.

