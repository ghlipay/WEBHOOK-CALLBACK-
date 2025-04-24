# LiPayKripto Webhook SDK

[English](#lipaykripto-webhook-sdk---english) | [Türkçe](#lipaykripto-webhook-sdk---türkçe)

---

## LiPayKripto Webhook SDK - English

### Introduction

LiPayKripto Webhook SDK is a simple library designed to help you validate and process webhook notifications from the LiPayKripto payment system. This SDK handles signature verification and provides a simple interface to process payment status updates.

### Installation

Simply copy the `LiPayKriptoWebhookSDK.php` file to your project.

### Usage

```php
<?php
require_once 'LiPayKriptoWebhookSDK.php';

// Initialize the SDK with your webhook secret key
$webhookSecret = 'your_webhook_secret_key'; // Provided by LiPayKripto
$webhook = new LiPayKriptoWebhookSDK($webhookSecret);

// Process an incoming webhook
$webhook->processWebhook(
    null, // Automatically reads from php://input if null
    
    // Callback when payment is confirmed
    function($payload) {
        // Payment successful
        $paymentId = $payload['paymentId'];
        $amount = $payload['tryAmount'];
        
        // Update your database or application state
        // Example: markPaymentAsComplete($paymentId, $amount);
    },
    
    // Callback when payment fails
    function($payload) {
        // Payment failed
        $paymentId = $payload['paymentId'];
        
        // Handle the failed payment
        // Example: markPaymentAsFailed($paymentId);
    },
    
    // Callback when signature verification fails
    function($payload) {
        // Security warning - invalid signature
        // Example: logSecurityWarning('Invalid webhook signature', $payload);
    }
);
```

### Manual Validation

If you need more control, you can manually verify the webhook signature:

```php
<?php
require_once 'LiPayKriptoWebhookSDK.php';

$webhookSecret = 'your_webhook_secret_key';
$webhook = new LiPayKriptoWebhookSDK($webhookSecret);

// Get the webhook data
$payload = json_decode(file_get_contents('php://input'), true);

// Verify the signature
if ($webhook->verifySignature($payload)) {
    // Signature is valid, process the webhook
    if ($payload['status'] === 'confirmed') {
        // Payment confirmed
    } else if ($payload['status'] === 'failed') {
        // Payment failed
    }
    
    // Send success response
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    // Invalid signature
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid signature']);
}
```

### Webhook Payload Structure

The webhook payload contains the following fields:

```json
{
  "success": true,
  "clientId": "12345",
  "status": "confirmed",  // or "failed"
  "tryAmount": "100.00",
  "paymentId": "PAYMENT123456",
  "signature": "a1b2c3d4e5f6..."
}
```

### Security Best Practices

1. Always verify the webhook signature to ensure it's coming from LiPayKripto
2. Keep your webhook secret key secure and don't expose it in client-side code
3. Process webhooks idempotently to handle potential duplicate notifications
4. Implement proper logging to track webhook processing

---

## LiPayKripto Webhook SDK - Türkçe

### Giriş

LiPayKripto Webhook SDK, LiPayKripto ödeme sisteminden gelen webhook bildirimlerini doğrulamanıza ve işlemenize yardımcı olmak için tasarlanmış basit bir kütüphanedir. Bu SDK, imza doğrulamasını yönetir ve ödeme durumu güncellemelerini işlemek için basit bir arayüz sağlar.

### Kurulum

`LiPayKriptoWebhookSDK.php` dosyasını projenize kopyalamanız yeterlidir.

### Kullanım

```php
<?php
require_once 'LiPayKriptoWebhookSDK.php';

// SDK'yı webhook secret key ile başlatın
$webhookSecret = 'your_webhook_secret_key'; // LiPayKripto tarafından sağlanır
$webhook = new LiPayKriptoWebhookSDK($webhookSecret);

// Gelen webhook'u işleyin
$webhook->processWebhook(
    null, // null ise otomatik olarak php://input'tan okur
    
    // Ödeme onaylandığında çağrılacak fonksiyon
    function($payload) {
        // Ödeme başarılı
        $paymentId = $payload['paymentId'];
        $amount = $payload['tryAmount'];
        
        // Veritabanınızı veya uygulama durumunuzu güncelleyin
        // Örnek: odemeBasariliOlarakIsaretle($paymentId, $amount);
    },
    
    // Ödeme başarısız olduğunda çağrılacak fonksiyon
    function($payload) {
        // Ödeme başarısız
        $paymentId = $payload['paymentId'];
        
        // Başarısız ödemeyi işleyin
        // Örnek: odemeBasarisizOlarakIsaretle($paymentId);
    },
    
    // İmza doğrulaması başarısız olduğunda çağrılacak fonksiyon
    function($payload) {
        // Güvenlik uyarısı - geçersiz imza
        // Örnek: guvenlikUyarisiKaydet('Geçersiz webhook imzası', $payload);
    }
);
```

### Manuel Doğrulama

Daha fazla kontrol istiyorsanız, webhook imzasını manuel olarak doğrulayabilirsiniz:

```php
<?php
require_once 'LiPayKriptoWebhookSDK.php';

$webhookSecret = 'your_webhook_secret_key';
$webhook = new LiPayKriptoWebhookSDK($webhookSecret);

// Webhook verilerini al
$payload = json_decode(file_get_contents('php://input'), true);

// İmzayı doğrula
if ($webhook->verifySignature($payload)) {
    // İmza geçerli, webhook'u işle
    if ($payload['status'] === 'confirmed') {
        // Ödeme onaylandı
    } else if ($payload['status'] === 'failed') {
        // Ödeme başarısız oldu
    }
    
    // Başarılı yanıt gönder
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    // Geçersiz imza
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Geçersiz imza']);
}
```

### Webhook Veri Yapısı

Webhook verisi aşağıdaki alanları içerir:

```json
{
  "success": true,
  "clientId": "12345",
  "status": "confirmed",  // veya "failed"
  "tryAmount": "100.00",
  "paymentId": "PAYMENT123456",
  "signature": "a1b2c3d4e5f6..."
}
```

### Güvenlik En İyi Uygulamaları

1. Webhook'un LiPayKripto'dan geldiğinden emin olmak için her zaman imzayı doğrulayın
2. Webhook secret key'inizi güvende tutun ve istemci tarafı kodunda açığa çıkarmayın
3. Olası çift bildirimleri işlemek için webhook'ları idempotent (aynı işlemi birden fazla kez güvenle yapabilecek şekilde) işleyin
4. Webhook işlemeyi izlemek için uygun loglama mekanizması kurun

---

© LiPayKripto, 2025