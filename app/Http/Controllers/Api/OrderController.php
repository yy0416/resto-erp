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
use App\Models\Customer;
use Illuminate\Support\Carbon;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // 自动识别/创建客户
            $customer = null;
            if ($request->filled('phone')) {
                $customer = Customer::where('phone', $request->phone)->first();
            }
            if (!$customer) {
                $customer = Customer::create([
                    'name' => $request->input('name', 'Client'),
                    'email' => $request->input('email', null),
                    'phone' => $request->input('phone', null),
                    'notes' => $request->input('notes', null),
                ]);
            }

            // 创建订单（先不算总价）
            $order = Order::create([
                'customer_id' => $customer->id,
                'restaurant_id' => $request->restaurant_id,
                'status' => Order::STATUS_PENDING,
                'total_price' => 0,
                'order_type' => $request->order_type,
                'table_number' => $request->order_type === 'dine_in' ? $request->table_number : null,
                'started_at' => Carbon::now(),
            ]);

            $total = 0;

            // 创建订单明细
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

            // 更新总价
            $order->update([
                'total_price' => $total,
            ]);

            return $order->load('items');
        });
    }

    /**
     * 🌟 升级后的订单列表接口
     * 同时兼容：厨房大屏（查全店未完成）和平板端（查单桌所有历史+带价格明细）
     */
    public function index(Request $request)
    {
        $query = Order::with('items.dish')->orderByDesc('started_at');

        // 💡 如果请求里带了 table_number，说明是平板端在查这桌的历史账单
        if ($request->has('table_number')) {
            $query->where('table_number', $request->table_number);
        } else {
            // 💡 如果没传桌号，说明是厨房大屏在查，只展示 pending 和 preparing 的订单
            $query->whereIn('status', ['pending', 'preparing']);
        }

        return OrderResource::collection($query->get());
    }

    // 🌟 修复强制类型提示警告
    public function show(int $id)
    {
        $order = Order::with('items.dish')->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * 🌟 升级：支持流转订单状态 (Pending -> Preparing -> Delivered)
     * 同时通过声明 int $id 彻底干掉 VS Code 的 P1132 报错
     */
    public function update(UpdateOrderStatusRequest $request, int $id)
    {
        $order = Order::findOrFail($id);
        $newStatus = $request->status;

        if (!Order::canChangeStatus($order->status, $newStatus)) {
            // 💡 修复：这里改用双引号，这样变量 {$order->status} 才能正确解析出来
            return response()->json(['message' => "Cannot change order status from {$order->status}"], 422);
        }

        $order->update(['status' => $newStatus]);
        return new OrderResource($order);
    }

    // 🌟 修复强制类型提示警告
    public function cancel(int $id)
    {
        $order = Order::findOrFail($id);

        if (!Order::canChangeStatus($order->status, Order::STATUS_CANCELLED)) {
            return response()->json(['message' => "Cannot cancel order from {$order->status}"], 422);
        }
        $order->update(['status' => 'cancelled']);
        return new OrderResource($order);
    }
}
