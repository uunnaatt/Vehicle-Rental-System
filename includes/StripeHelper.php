<?php
// includes/StripeHelper.php

require_once __DIR__ . '/../config/stripe.php';

class StripeHelper {
    private $secretKey;

    public function __construct() {
        $this->secretKey = STRIPE_SECRET_KEY;
    }

    private function makeRequest($endpoint, $method = 'GET', $data = []) {
        $url = "https://api.stripe.com/v1/" . $endpoint;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $headers = [
            "Authorization: Bearer " . $this->secretKey
        ];

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                $postFields = http_build_query($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                $headers[] = "Content-Type: application/x-www-form-urlencoded";
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    public function createCheckoutSession($amount, $currency, $name, $successUrl, $cancelUrl, $clientReferenceId) {
        $data = [
            'payment_method_types[0]' => 'card',
            'line_items[0][price_data][currency]' => $currency,
            'line_items[0][price_data][product_data][name]' => $name,
            'line_items[0][price_data][unit_amount]' => $amount, // amount in cents
            'line_items[0][quantity]' => 1,
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $clientReferenceId
        ];

        return $this->makeRequest('checkout/sessions', 'POST', $data);
    }

    public function retrieveSession($sessionId) {
        return $this->makeRequest('checkout/sessions/' . $sessionId, 'GET');
    }
}
?>
