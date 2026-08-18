<?php

class GeniusPayConfig
{
    public static function get(): array
    {
        $mode = getenv('GENIUSPAY_MODE') ?: 'sandbox';
        return [
            'mode' => $mode,
            'webhook_secret' => $mode === 'sandbox'
                ? (getenv('GENIUSPAY_WEBHOOK_SECRET_SANDBOX') ?: 'whsec_sandbox_lavex_default_secret_key_2026')
                : (getenv('GENIUSPAY_WEBHOOK_SECRET_LIVE') ?: 'whsec_live_lavex_production_secret_key_2026'),
            'api_key' => $mode === 'sandbox'
                ? (getenv('GENIUSPAY_API_KEY_SANDBOX') ?: 'sk_sandbox_3cb4Hr013eAsOkvp66bwhxby0ZEhSUL8')
                : (getenv('GENIUSPAY_API_KEY_LIVE') ?: 'gp_live_key'),
            'api_secret' => $mode === 'sandbox'
                ? (getenv('GENIUSPAY_API_SECRET_SANDBOX') ?: 'ss_sandbox_npYoZWl2BvIbwGpPBwAuZ9GW1TcDiQyDC0tTPHMgfZukbujY')
                : (getenv('GENIUSPAY_API_SECRET_LIVE') ?: ''),
            'api_url' => $mode === 'sandbox'
                ? 'https://api.sandbox.geniuspay.ci/v1'
                : 'https://api.geniuspay.ci/v1',
            'commission_rate' => 0.00, // 0% pour le lancement MVP
            'minimum_retrait' => 2000.00, // 2 000 FCFA minimum
        ];
    }
}
