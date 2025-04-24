# LiPayKripto Webhook Integration Guide

This document explains how LiPayKripto's webhook system works and how to integrate it into your application.

## Table of Contents

1. [What is a Webhook?](#what-is-a-webhook)
2. [Webhook Structure](#webhook-structure)
3. [Webhook Verification (HMAC-SHA256)](#webhook-verification-hmac-sha256)
4. [Sample Webhook Processing Code](#sample-webhook-processing-code)

## What is a Webhook?

A webhook is a mechanism that allows the LiPayKripto system to automatically send notifications to your server when a payment or withdrawal transaction is completed. This eliminates the need to manually check the status of your users' payments; the system automatically notifies you.

LiPayKripto sends two types of webhooks:
- **Payment Webhooks**: Sent when a cryptocurrency payment is completed or fails
- **Withdrawal Webhooks**: Sent when a cryptocurrency withdrawal is completed or fails

## Webhook Structure

### Payment Webhooks

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

### Withdrawal Webhooks

```json
{
  "success": true,
  "clientId": "12345",
  "status": "confirmed",  // or "failed"
  "tryAmount": "100.00",
  "paymentId": "WITHDRAW123456",
  "signature": "a1b2c3d4e5f6..."
}
```

### Field Descriptions

| Field | Type | Description |
|------|-----|----------|
| `success` | boolean | Indicates whether the webhook was successfully sent |
| `clientId` | string | Your API client ID |
| `status` | string | Transaction status: "confirmed" or "failed" |
| `tryAmount` | string | Transaction amount (in TRY) |
| `paymentId` | string | Your transaction reference number (sent when creating payment/withdrawal) |
| `signature` | string | HMAC-SHA256 signature (for security verification) |

## Webhook Verification (HMAC-SHA256)

LiPayKripto uses HMAC-SHA256 signing to ensure the security of webhooks. This allows you to verify that received webhooks genuinely come from LiPayKripto and that their content has not been altered.

### Signature Verification Process

1. Receive the webhook request
2. Separate the `signature` field
3. Sign the remaining data with the same secret key
4. Compare your generated signature with the received `signature` value
5. If they match, the webhook is valid

The secret key will be provided to you by LiPayKripto. You should store this key securely on your server side.

## Webhook Structure and Delivery

Webhook notifications from LiPayKripto's system are sent as **HTTP POST** requests. We send webhooks to you in JSON format using the POST method.

### Webhook Delivery

LiPayKripto system sends webhook notifications via the **POST** method in the following situations:
- When a payment is confirmed
- When a payment fails
- When a withdrawal transaction is completed
- When a withdrawal transaction fails

### Webhook Content

The content of the webhooks sent to you is as follows:

```json
{
  "success": true,
  "clientId": "YOUR_CLIENT_ID",
  "status": "confirmed",  // or "failed"
  "tryAmount": "100.00",
  "paymentId": "PAYMENT_REFERENCE_NUMBER",
  "signature": "HMAC_SHA256_SIGNATURE"
}
```

**Important:** For signature verification, you should first remove the `signature` field from the incoming webhook content and sign the remaining data using the WEBHOOK_SECRET_KEY with the HMAC-SHA256 algorithm. The signature you generate should match the received signature.

## Sample Webhook Processing Code

### Webhook Receipt and Validation with PHP

```php
<?php
// Get webhook request (sent via POST method)
$payload = json_decode(file_get_contents('php://input'), true);
$receivedSignature = $payload['signature'] ?? '';

// Remove signature from payload
$payloadWithoutSignature = $payload;
unset($payloadWithoutSignature['signature']);

// Sign with HMAC-SHA256
$secretKey = 'your_webhook_secret_key'; // Key provided by LiPayKripto
$expectedSignature = hash_hmac('sha256', json_encode($payloadWithoutSignature), $secretKey);

// Compare signatures
if (hash_equals($expectedSignature, $receivedSignature)) {
    // Signature is correct, webhook is valid
    $status = $payload['status'];
    $paymentId = $payload['paymentId'];
    $amount = $payload['tryAmount'];
    
    if ($status === 'confirmed') {
        // Payment confirmed, add credit to user or complete the order
        // Update your database
    } else {
        // Payment failed, inform the user
    }
    
    // Send successful response to LiPayKripto
    http_response_code(200);
    echo json_encode(['success' => true]);
} else {
    // Signature invalid, webhook rejected
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid signature']);
}
```

---

This document is the official documentation for the LiPayKripto Webhook Integration Guide. For questions, please contact our support team.

© LiPayKripto, 2025