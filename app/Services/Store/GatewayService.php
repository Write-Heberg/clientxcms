<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
namespace App\Services\Store;

use App\Models\Core\Gateway;
use Illuminate\Support\Facades\Cache;

class GatewayService
{
    /**
     * Get available gateways
     * 0 to get only balance gateways
     * Any other number to get all gateways with amount
     * @param float $amount
     * @return array|void
     */
    public static function getAvailable(float $amount = 0)
    {
        if (!is_installed()){
            return [];
        }
        $gateways = Cache::remember('gateways', 60 * 60 * 24, function () {
            return Gateway::getAvailable()->get();
        });
        if ($amount == 0){
            return $gateways->filter(function ($gateway) {
                return $gateway->uuid == 'balance';
            });
        }
        return $gateways;
    }

    public static function forgotAvailable()
    {
        Cache::forget('gateways');
    }
}
