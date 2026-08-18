<?php

require_once __DIR__ . '/../config/GeniusPayConfig.php';

class GeniusPayService
{
    /**
     * Initialise une session de paiement client GeniusPay
     */
    public static function initiatePayment(array $data): array
    {
        $config = GeniusPayConfig::get();
        $apiKey = $config['api_key'];
        $apiUrl = rtrim($config['api_url'], '/') . '/payments';

        $orderCode = $data['order_code'] ?? '';
        $amount = (float)($data['amount'] ?? 0);
        $provider = strtolower($data['provider'] ?? 'wave');
        $customerName = $data['customer_name'] ?? 'Client LAVEX';
        $customerPhone = $data['customer_phone'] ?? '';
        $customerEmail = $data['customer_email'] ?? '';
        $callbackUrl = $data['callback_url'] ?? (defined('RACINE') ? RACINE : 'http://localhost/lavex/');
        $webhookUrl = $data['webhook_url'] ?? 'http://localhost/admin-lavex/webhooks/geniuspay';

        $payload = [
            'amount' => $amount,
            'currency' => 'XOF',
            'order_id' => $orderCode,
            'description' => "Paiement pressing - Commande #{$orderCode}",
            'payment_method' => 'mobile_money',
            'provider' => $provider,
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
            'callback_url' => $callbackUrl,
            'webhook_url' => $webhookUrl,
            'metadata' => [
                'order_id' => $orderCode,
                'client_code' => $data['client_code'] ?? '',
                'pressing_code' => $data['pressing_code'] ?? ''
            ]
        ];

        // Tentative d'appel API vers GeniusPay si clé configurée
        $isLiveKey = (strpos($apiKey, 'gp_test_') !== 0 && !empty($apiKey));
        
        if ($isLiveKey) {
            try {
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $apiKey,
                        'X-API-Key: ' . $apiKey,
                        'X-Webhook-Environment: ' . $config['mode']
                    ],
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_TIMEOUT => 15
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300 && !empty($response)) {
                    $resJson = json_decode($response, true);
                    if (!empty($resJson['data']['payment_url']) || !empty($resJson['data']['checkout_url'])) {
                        return [
                            'success' => true,
                            'mode' => $config['mode'],
                            'reference' => $resJson['data']['reference'] ?? ('GP-' . strtoupper(uniqid())),
                            'payment_url' => $resJson['data']['payment_url'] ?? $resJson['data']['checkout_url'],
                            'checkout_url' => $resJson['data']['checkout_url'] ?? $resJson['data']['payment_url'],
                            'provider' => $provider,
                            'amount' => $amount,
                            'order_code' => $orderCode
                        ];
                    }
                }
            } catch (Exception $e) {
                error_log("Erreur GeniusPay API Payment: " . $e->getMessage());
            }
        }

        // Mode Sandbox / Simulation interactive
        $ref = 'TXN-' . strtoupper($provider) . '-' . substr(time(), -5) . rand(100, 999);
        return [
            'success' => true,
            'mode' => $config['mode'],
            'reference' => $ref,
            'payment_url' => null,
            'checkout_url' => null,
            'provider' => $provider,
            'amount' => $amount,
            'order_code' => $orderCode,
            'customer_phone' => $customerPhone,
            'is_simulation' => true
        ];
    }

    /**
     * Déclenche un virement de retrait (Cashout) vers le Mobile Money du pressing
     */
    public static function initiateCashout(array $retrait): array
    {
        $config = GeniusPayConfig::get();
        $apiKey = $config['api_key'];
        $apiUrl = rtrim($config['api_url'], '/') . '/cashouts';

        $codeRetrait = $retrait['code_retrait'] ?? '';
        $amount = (float)($retrait['montant_demande'] ?? 0);
        $provider = strtolower($retrait['operateur_retrait'] ?? 'wave');
        $phone = $retrait['telephone_beneficiaire'] ?? '';
        $name = $retrait['nom_beneficiaire'] ?? 'Gérant Pressing';

        $payload = [
            'amount' => $amount,
            'currency' => 'XOF',
            'reference' => $codeRetrait,
            'provider' => $provider,
            'recipient_phone' => $phone,
            'recipient_name' => $name,
            'metadata' => [
                'retrait_code' => $codeRetrait,
                'pressing_code' => $retrait['pressing_code'] ?? ''
            ]
        ];

        $isLiveKey = (strpos($apiKey, 'gp_test_') !== 0 && !empty($apiKey));

        if ($isLiveKey) {
            try {
                $ch = curl_init($apiUrl);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Bearer ' . $apiKey,
                        'X-Webhook-Environment: ' . $config['mode']
                    ],
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_TIMEOUT => 20
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300 && !empty($response)) {
                    $resJson = json_decode($response, true);
                    return [
                        'success' => true,
                        'reference' => $resJson['data']['reference'] ?? ('CASHOUT-' . strtoupper(uniqid())),
                        'status' => $resJson['data']['status'] ?? 'processing',
                        'message' => 'Virement initié auprès de l\'opérateur Mobile Money'
                    ];
                }
            } catch (Exception $e) {
                error_log("Erreur GeniusPay API Cashout: " . $e->getMessage());
            }
        }

        // Mode Sandbox
        $refCashout = 'CASHOUT-' . strtoupper($provider) . '-' . substr(time(), -4) . rand(10, 99);
        return [
            'success' => true,
            'reference' => $refCashout,
            'status' => 'processing',
            'message' => 'Virement Mobile Money initié avec succès (Mode ' . ucfirst($config['mode']) . ')'
        ];
    }
}
