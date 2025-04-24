<?php
/**
 * LiPayKripto Webhook SDK - PHP
 * 
 * This SDK is designed to validate and process webhook notifications from LiPayKripto.
 * 
 * @version 1.0.0
 * @author LiPayKripto
 */

class LiPayKriptoWebhookSDK {
    /**
     * Webhook secret key
     * @var string
     */
    private $secretKey;
    
    /**
     * Initialize the SDK
     * 
     * @param string $secretKey Secret key used to validate webhook signatures
     */
    public function __construct($secretKey) {
        $this->secretKey = $secretKey;
    }
    
    /**
     * Validates and processes webhook data
     * 
     * @param array|null $payload Webhook data (if null, automatically reads from php://input)
     * @param callable $onConfirmed Function to call when payment is confirmed
     * @param callable $onFailed Function to call when payment fails
     * @param callable $onInvalidSignature Function to call when signature validation fails
     * @return bool Was the processing successful?
     */
    public function processWebhook($payload = null, $onConfirmed = null, $onFailed = null, $onInvalidSignature = null) {
        // If payload is null, automatically read from PHP input
        if ($payload === null) {
            $payload = json_decode(file_get_contents('php://input'), true);
        }
        
        // Check if webhook data is in the correct format
        if (!$this->validatePayloadFormat($payload)) {
            $this->respond(false, 'Invalid payload format');
            return false;
        }
        
        // Verify the signature
        if (!$this->verifySignature($payload)) {
            if ($onInvalidSignature !== null) {
                call_user_func($onInvalidSignature, $payload);
            }
            $this->respond(false, 'Invalid signature');
            return false;
        }
        
        // Call appropriate callbacks based on transaction status
        if ($payload['status'] === 'confirmed') {
            if ($onConfirmed !== null) {
                call_user_func($onConfirmed, $payload);
            }
            $this->respond(true, 'Webhook processed successfully');
            return true;
        } else if ($payload['status'] === 'failed') {
            if ($onFailed !== null) {
                call_user_func($onFailed, $payload);
            }
            $this->respond(true, 'Webhook processed successfully');
            return true;
        }
        
        // Unknown status
        $this->respond(false, 'Unknown status');
        return false;
    }
    
    /**
     * Verifies the signature
     * 
     * @param array $payload Webhook data
     * @return bool Is the signature valid?
     */
    public function verifySignature($payload) {
        // Get the received signature
        $receivedSignature = isset($payload['signature']) ? $payload['signature'] : '';
        
        // Remove signature from data object
        $payloadWithoutSignature = $payload;
        unset($payloadWithoutSignature['signature']);
        
        // Create expected signature
        $expectedSignature = hash_hmac('sha256', json_encode($payloadWithoutSignature), $this->secretKey);
        
        // Compare signatures (safe against timing attacks)
        return hash_equals($expectedSignature, $receivedSignature);
    }
    
    /**
     * Checks if the webhook payload format is valid
     * 
     * @param array $payload Webhook data
     * @return bool Is the payload valid?
     */
    private function validatePayloadFormat($payload) {
        if (!is_array($payload)) {
            return false;
        }
        
        // Check for required fields
        $requiredFields = ['success', 'clientId', 'status', 'tryAmount', 'paymentId', 'signature'];
        foreach ($requiredFields as $field) {
            if (!isset($payload[$field])) {
                return false;
            }
        }
        
        // Is the status value valid?
        if (!in_array($payload['status'], ['confirmed', 'failed'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Sends HTTP response
     * 
     * @param bool $success Was the operation successful?
     * @param string $message Operation message
     */
    private function respond($success, $message) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
    }
}