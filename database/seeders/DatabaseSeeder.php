<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Restaurant;
use App\Models\Customer;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. User（可选，用于后续 auth）
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 2. Restaurants
        $restaurants = Restaurant::factory(3)->create();

        // 3. Customers
        $customers = Customer::factory(5)->create();

        // 4. Dishes（绑定 restaurant）
        $dishes = Dish::factory(20)->make()->each(function ($dish) use ($restaurants) {
            $dish->restaurant_id = $restaurants->random()->id;
            $dish->save();
        });

        // 5. Orders（绑定 restaurant + customer）
        $orders = Order::factory(10)->make()->each(function ($order) use ($customers, $restaurants) {
            $order->customer_id = $customers->random()->id;
            $order->restaurant_id = $restaurants->random()->id;
            $order->save();
        });

        // 6. OrderItems（绑定 order + dish）
        foreach ($orders as $order) {
            OrderItem::factory(rand(1, 3))->create([
                'order_id' => $order->id,
                'dish_id' => $dishes->random()->id,
            ]);
        }
    }
}
