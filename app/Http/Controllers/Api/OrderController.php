<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\OrderResource;
use App\Http\Requests\UpdateOrderStatusRequest;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {

            // 1️⃣ 创建订单（先不算总价）
            $order = Order::create([
                'customer_id' => $request->customer_id,
                'restaurant_id' => $request->restaurant_id,
                'status' => 'pending',
                'total_price' => 0,
            ]);

            $total = 0;

            // 2️⃣ 创建订单明细
            foreach ($request->items as $item) {
                $dish = Dish::findOrFail($item['dish_id']);

                $linePrice = $dish->price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $dish->id,
                    'quantity' => $item['quantity'],
                    'price' => $linePrice,
                ]);

                $total += $linePrice;
            }

            // 3️⃣ 更新总价
            $order->update([
                'total_price' => $total,
            ]);

            return $order->load('items');
        });
    }

    public function index(Request $request)
    {
        $query = Order::with('items.dish');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return OrderResource::collection($query->get());
    }


    public function show($id)
    {
        $order = Order::with('items.dish')->findOrFail($id);
        return new OrderResource($order);
    }

    public function update(UpdateOrderStatusRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->status;

        if (!Order::canChangeStatus($order->status, $newStatus)) {
            return response()->json(['message' => 'Cannot change order status from {$order->status}'], 422);
        }

        $order->update(['status' => $newStatus]);
        return new OrderResource($order);
    }

    public function cancel($id)
    {
        $order = Order::findOrFail($id);

        if (!Order::canChangeStatus($order->status, Order::STATUS_CANCELLED)) {
            return response()->json(['message' => "Cannot cancel order from {$order->status}"], 422);
        }
        $order->update(['status' => 'cancelled']);
        return new OrderResource($order);
    }
}
