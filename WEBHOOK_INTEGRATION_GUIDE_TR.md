# LiPayKripto Webhook Entegrasyon Rehberi

Bu belge, LiPayKripto'nun webhook sisteminin nasıl çalıştığını ve uygulamanıza nasıl entegre edeceğinizi açıklar.

## İçindekiler

1. [Webhook Nedir?](#webhook-nedir)
2. [Webhook Yapısı](#webhook-yapısı)
3. [Webhook Doğrulama (HMAC-SHA256)](#webhook-doğrulama-hmac-sha256)
4. [Örnek Webhook İşleme Kodları](#örnek-webhook-işleme-kodları)

## Webhook Nedir?

Webhook, bir ödeme veya çekim işlemi tamamlandığında, LiPayKripto sisteminin sizin sunucunuza otomatik olarak bildirim göndermesini sağlayan bir mekanizmadır. Bu sayede, kullanıcılarınızın ödeme durumlarını manuel olarak kontrol etmenize gerek kalmaz; sistem otomatik olarak bildirim yapar.

LiPayKripto iki tür webhook gönderir:
- **Ödeme Webhook'ları**: Bir kripto para ödemesi tamamlandığında veya başarısız olduğunda gönderilir
- **Çekim Webhook'ları**: Bir kripto para çekim işlemi tamamlandığında veya başarısız olduğunda gönderilir

## Webhook Yapısı

### Ödeme Webhook'ları

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

### Çekim Webhook'ları

```json
{
  "success": true,
  "clientId": "12345",
  "status": "confirmed",  // veya "failed"
  "tryAmount": "100.00",
  "paymentId": "WITHDRAW123456",
  "signature": "a1b2c3d4e5f6..."
}
```

### Alan Açıklamaları

| Alan | Tip | Açıklama |
|------|-----|----------|
| `success` | boolean | Webhook'un başarılı bir şekilde gönderilip gönderilmediğini belirtir |
| `clientId` | string | API istemci kimliğiniz |
| `status` | string | İşlem durumu: "confirmed" (onaylandı) veya "failed" (başarısız) |
| `tryAmount` | string | İşlem tutarı (TL cinsinden) |
| `paymentId` | string | İşlem referans numaranız (ödeme/çekim oluştururken gönderdiğiniz) |
| `signature` | string | HMAC-SHA256 imzası (güvenlik doğrulaması için) |

## Webhook Doğrulama (HMAC-SHA256)

LiPayKripto, webhook'ların güvenliğini sağlamak için HMAC-SHA256 imzalama kullanır. Bu, alınan webhook'ların gerçekten LiPayKripto'dan geldiğini ve içeriğinin değiştirilmediğini doğrulamanıza olanak tanır.

### İmza Doğrulama Süreci

1. Webhook isteğini alın
2. `signature` alanını ayırın
3. Kalan veriyi aynı secret key ile imzalayın
4. Oluşturduğunuz imzayı, gelen `signature` değeri ile karşılaştırın
5. Eşleşiyorsa, webhook geçerlidir

Secret key, LiPayKripto tarafından size iletilecektir. Bu anahtarı sunucu tarafında güvenli bir şekilde saklamalısınız.

## Webhook Yapısı ve Gönderimi

LiPayKripto sisteminden gelen webhook bildirimleri **HTTP POST** isteği olarak gönderilir. Biz size webhook'ları gönderirken JSON formatında ve POST metoduyla göndeririz.

### Webhook Gönderimi

LiPayKripto sistemi, aşağıdaki durumlarda webhook bildirimlerini **POST** metodu ile size gönderir:
- Bir ödeme onaylandığında
- Bir ödeme başarısız olduğunda
- Bir çekim işlemi tamamlandığında
- Bir çekim işlemi başarısız olduğunda

### Webhook İçeriği

Size gönderilen webhook'ların içeriği aşağıdaki gibidir:

```json
{
  "success": true,
  "clientId": "SIZE_ÖZEL_CLIENT_ID",
  "status": "confirmed",  // veya "failed"
  "tryAmount": "100.00",
  "paymentId": "ÖDEME_REFERANS_NUMARASI",
  "signature": "HMAC_SHA256_İMZASI"
}
```

**Önemli:** İmza doğrulaması için, gelen webhook içeriğinden öncelikle `signature` alanını çıkarmalı ve kalan veriyi WEBHOOK_SECRET_KEY ile HMAC-SHA256 algoritması kullanarak imzalamalısınız. Oluşturduğunuz imza, gelen imza ile eşleşmelidir.

## Örnek Webhook İşleme Kodları

### PHP İle Webhook Alımı ve Doğrulaması

```php
<?php
// Webhook isteğini al (POST metodu ile gönderilir)
$payload = json_decode(file_get_contents('php://input'), true);
$receivedSignature = $payload['signature'] ?? '';

// İmzayı payload'dan çıkar
$payloadWithoutSignature = $payload;
unset($payloadWithoutSignature['signature']);

// HMAC-SHA256 ile imzala
$secretKey = 'your_webhook_secret_key'; // LiPayKripto tarafından sağlanan anahtar
$expectedSignature = hash_hmac('sha256', json_encode($payloadWithoutSignature), $secretKey);

// İmzaları karşılaştır
if (hash_equals($expectedSignature, $receivedSignature)) {
    // İmza doğru, webhook geçerli
    $status = $payload['status'];
    $paymentId = $payload['paymentId'];
    $amount = $payload['tryAmount'];
    
    if ($status === 'confirmed') {
        // Ödeme onaylandı, kullanıcıya kredi ekle veya siparişi tamamla
        // Veritabanınızı güncelleyin
    } else {
        // Ödeme başarısız oldu, kullanıcıyı bilgilendir
    }
    
    // LiPayKripto'ya başarılı yanıt gönder
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    // İmza geçersiz, webhook reddedildi
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid signature']);
}
```

---

Bu belge, LiPayKripto Webhook Entegrasyon Rehberinin resmi dokümantasyonudur. Sorularınız için destek ekibimizle iletişime geçebilirsiniz.

© LiPayKripto, 2025
