<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity  = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 500, 10000);

        return [
            'order_id'=> Order::factory(),
            'menu_item_id'=> MenuItem::factory(),
            'quantity'=> $quantity,
            'unit_price'=> $unitPrice,
            'subtotal'=> $quantity * $unitPrice,
        ];
    }
}
