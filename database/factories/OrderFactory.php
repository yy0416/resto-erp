<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(), // 关联客户
            'total_price' => $this->faker->randomFloat(2, 10, 200), // 10~200 €
            'status' => $this->faker->randomElement(['pending', 'paid', 'delivered', 'cancelled']),
        ];
    }
}
