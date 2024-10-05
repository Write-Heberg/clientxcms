<?php

namespace Database\Seeders;

use App\Core\Gateway\BalanceType;
use App\Core\Gateway\BankTransfertType;
use App\Core\Gateway\NoneGatewayType;
use App\Core\Gateway\PayPalExpressCheckoutType;
use App\Core\Gateway\PayPalMethodType;
use App\Core\Gateway\StripeType;
use App\Models\Core\Gateway;
use Illuminate\Database\Seeder;

class GatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Gateway::count() > 0) {
            foreach([BankTransfertType::UUID, PayPalExpressCheckoutType::UUID, PayPalMethodType::UUID, BalanceType::UUID, StripeType::UUID, NoneGatewayType::UUID] as $uuid) {
                $first = Gateway::where('uuid', $uuid)->first();
                if ($first) {
                    $other = Gateway::where('uuid', $uuid)->where('id','!=', $first->id)->get();
                    foreach ($other as $gateway) {
                        $gateway->delete();
                    }
                }
            }
            return;
        }
        Gateway::updateOrCreate([
            'name' => 'Virement Bancaire',
            'uuid' => BankTransfertType::UUID,
            'status' => 'unreferenced',
        ]);
        Gateway::updateOrCreate([
            'name' => 'PayPal Express Checkout',
            'uuid' => PayPalExpressCheckoutType::UUID,
            'status' => 'active',
        ]);
        Gateway::updateOrCreate([
            'name' => 'PayPal',
            'uuid' => PayPalMethodType::UUID,
            'status' => 'hidden',
        ]);
        Gateway::updateOrCreate([
            'name' => 'Balance',
            'uuid' => BalanceType::UUID,
            'status' => 'active',
        ]);
        Gateway::updateOrCreate([
            'name' => 'Carte bancaire',
            'uuid' => StripeType::UUID,
            'status' => 'active',
        ]);
        Gateway::updateOrCreate([
            'name' => 'Aucun',
            'uuid' => NoneGatewayType::UUID,
            'status' => 'hidden',
        ]);

    }
}
