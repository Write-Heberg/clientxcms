<?php

namespace Database\Factories\Store;

use App\Models\Store\Group;
use App\Models\Store\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store\Product>
 */
class ProductFactory extends Factory
{

    protected $model = \App\Models\Store\Product::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'group_id' => $this->faker->numberBetween(Group::first()->id, Group::latest()->first()->id + 9),
            'status' => $this->faker->randomElement(['active', 'hidden']),
            'description' => $this->faker->sentence,
            'sort_order' => $this->faker->randomDigit,
            'type' => 'pterodactyl',
            'stock' => $this->faker->randomDigit,
        ];
    }
}
