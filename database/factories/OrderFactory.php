<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number'=>'ORD-'.strtoupper(Str::random(10)),
            'user_id'=>optional(User::inRandomOrder()->first())->id ?? User::factory()->create()->id,
            'total_amount'=>fake()->randomFloat(2,5000,10000),
            'delivery_fee'=>fake()->randomElement([1000,1500,2000,3000,4000]),
            'delivery_address'=>fake()->address(),
            'delivery_phone'=>'09'.fake()->numerify('#########'),
            'status'=>fake()->randomElement(['pending','confirmed','preparing','delivering','completed','cancelled']),
            'payment_method'=>fake()->randomElement(['cod','kbzpay','wavepay']),
            'payment_status'=>fake()->randomElement(['paid','unpaid']),
            'notes'=>fake()->optional(0.3)->sentence()
        ];
        
    }
}
