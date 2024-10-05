<?php

namespace Database\Factories\Provisioning;

use App\Models\Account\Customer;
use App\Models\Core\InvoiceItem;
use App\Models\Provisioning\Server;
use App\Models\Provisioning\Service;
use App\Models\Store\Group;
use App\Models\Store\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Product>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Minecraft Charbon', 'Minecraft Gold', 'Minecraft Diamant']),
            'customer_id' => Customer::first()->id,
            'type' => 'pterodactyl',
            'price' => 29.99,
            'billing' => 'quarterly',
            'initial_price' => 29.99,
            'server_id' => Server::first()->id,
            'product_id' => Product::first()->id,
            'invoice_id' => null,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMonths(3),
            'data' => json_encode(['currency' => 'EUR', 'billing' => 'quarterly'])
        ];
    }
}
