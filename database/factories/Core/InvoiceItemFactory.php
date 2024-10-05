<?php

namespace Database\Factories\Core;

use App\Models\Core\Invoice;
use App\Models\Core\InvoiceItem;
use App\Models\Store\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory()->create()->id,
            'description' => $this->faker->text(),
            'name' => $this->faker->randomElement(['Minecraft Charbon', 'Minecraft Gold', 'Minecraft Diamant']),
            'quantity' => 1,
            'unit_price' => 1,
            'unit_setupfees' => 0,
            'type' => 'service',
            'related_id' => Product::first()->id,
            'data' => json_encode(["billing" => 'monthly', 'currency' => 'EUR'])
        ];
    }


    public function modelName()
    {
        return InvoiceItem::class;
    }
}
