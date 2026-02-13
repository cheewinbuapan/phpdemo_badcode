<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . time() . '-' . fake()->numerify('####'),
            'user_id' => User::factory(),
            'status_id' => ProductStatus::PENDING,
            'shipping_address' => null,
            'total_amount' => fake()->randomFloat(2, 50, 5000),
        ];
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => ProductStatus::CONFIRMED,
            'shipping_address' => fake()->address(),
        ]);
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => ProductStatus::PENDING,
            'shipping_address' => null,
        ]);
    }

    /**
     * Set a specific user for the order.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->user_id,
        ]);
    }
}
