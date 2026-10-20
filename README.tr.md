# 📡 WooCommerce Dayanıklı Webhook & Dead-Letter Dağıtıcısı

[🇬🇧 English](README.md) · [🇹🇷 Türkçe](README.tr.md)

> Harici ERP, CRM (HubSpot, Klaviyo) veya kargo sistemlerine gönderilen webhook çağrılarının zaman aşımı veya 500 hatalarında kaybolmasını engelleyen, üstel geri çekilme (exponential backoff) algoritmasıyla otomatik tekrar deneyen kuyruk sistemi.

Geliştirici ve Bakım Sorumlusu: **[Harun Sekmen](https://github.com/harunsekmen)** — E-Ticaret Sistem Mühendisi & WooCommerce Uzmanı.

[✉️ İletişime Geç](mailto:sekmenwp@gmail.com) · [💼 Upwork Profili](https://www.upwork.com/freelancers/~01a7a8b58043e6e6d9) · [🔗 GitHub Ana Profil](https://github.com/harunsekmen)

---

## 📸 Görsel Önizlemeler ve Yönetim Paneli

### 1. WordPress Yönetici Dead-Letter Konsolu
Başarısız çağrıların HTTP yanıt kodlarını, JSON gövdelerini gösteren ve tek tıkla tekrar tetikleme sunan yönetim paneli:

![Webhook Dead-Letter Yönetim Paneli](docs/assets/admin-webhook-dlq-dashboard.png)

---

## ⚡ Çözdüğü Kritik Sorun

WooCommerce'in varsayılan webhook mekanizması harici servislerin anlık kesintilerine karşı savunmasızdır:
- **Sessizce Kaybolan Siparişler:** Karşı ERP veya CRM sunucusu 2 saniye geç yanıt verse veya geçici 500 hatası atsa webhook düşer.
- **Kargoya Verilmeyen Ürünler:** Fatura kesilmez, kargo etiketi oluşmaz ve mağaza sahibinin bundan günler sonra müşteri şikayetiyle haberi olur.

**WooCommerce Webhook Resilience**, başarısız çağrıları Action Scheduler kuyruğuna alarak akıllı aralıklarla (1dk, 5dk, 15dk, 1saat, 6saat) otomatik 5 kez tekrar dener.

---

## 🛠️ Öne Çıkan Özellikler

- **Otomatik Yeniden Deneme (Backoff):** Geçici kesintilerde istekleri kaybetmeden üstel geri çekilme ile tekrar iletir.
- **Dead-Letter Konsolu:** Kesin olarak başarısız olan isteklerin tam JSON yükünü yönetici panelinde saklar.
- **Tek Tıkla Yeniden Gönderim (Replay):** Karşı sunucu düzeldiğinde bekleyen tüm bildirimleri tek tuşla tekrar gönderir.
- **HPOS Uyumlu:** Sipariş durum değişikliklerini yeni veri yapısından anlık olarak yakalar.

---

## 🏗️ Teknik Mimari ve Standartlar

`PHP 8.1+` • `WooCommerce HPOS` • `Action Scheduler` • `WP REST API` • `Dead-Letter Queue Pattern`

---

## 🚀 Kurulum ve Test

1. Eklentiyi klonlayın ve test edin:
   ```bash
   git clone https://github.com/harunsekmen/woo-webhook-resilience.git wp-content/plugins/woo-webhook-resilience
   ```
2. Testi çalıştırın:
   ```bash
   php tests/test-webhook.php
   ```

---

## 📄 Lisans

Bu proje **GPL-3.0 Lisansı** ile açık kaynak olarak sunulmaktadır.
