<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * Get the current EUR exchange rate from USD.
     *
     * @return float
     */
    public function getExchangeRate(): float
    {
        try {
            $response = Http::timeout(5)
                ->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['rates']['EUR'])) {
                    return $data['rates']['EUR'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch exchange rate: ' . $e->getMessage());
        }

        return config('app.exchange_rate', 0.85);
    }
} 