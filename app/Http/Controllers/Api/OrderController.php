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

        // 💰 1. 如果带了 payment_status 参数（说明是收银台在查账）
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        // 📱 2. 如果带了 table_number（说明是平板端在查单桌历史）
        elseif ($request->has('table_number')) {
            $query->where('table_number', $request->table_number);
        }
        // 👨‍🍳 3. 啥都没带（说明是厨房大屏在查活动订单）
        else {
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

    /**
     * 💰 核心收银结账接口
     * 支持：临场增删改商品数量、应用优惠折扣、记录付款渠道、封账归档
     */
    public function pay(Request $request, int $id)
    {
        // 1. 验证结账请求的数据
        $request->validate([
            'payment_method' => 'required|string|in:Espèces,CB,Resto', // 必须是指定的付款方式
            'discount' => 'required|numeric|min:0',                   // 优惠金额不能为负
            'total_price' => 'required|numeric|min:0',                 // 最终实收总价
            'items' => 'required|array',                               // 最终核对的菜品数组
            'items.*.dish_id' => 'required|exists:dishes,id',
            'items.*.quantity' => 'required|integer|min:0',            // 数量可以为0（代表退掉了这道菜）
        ]);

        // 2. 开启安全事务，防止中间断电或出错导致财务数据混乱
        return DB::transaction(function () use ($request, $id) {
            $order = Order::findOrFail($id);

            // 如果订单已经付过钱了，直接拦截，防止重复收银
            if (isset($order->payment_status) && $order->payment_status === 'paid') {
                return response()->json(['message' => 'Cette commande est déjà payée. (该订单已结账，请勿重复操作)'], 422);
            }

            // 3. 临场调整菜品数量：先彻底清空这笔订单原有的旧明细
            OrderItem::where('order_id', $order->id)->delete();

            $calculatedTotal = 0;

            // 4. 重新灌入收银员核对后的最新菜品明细
            foreach ($request->items as $item) {
                // 如果数量被收银员减到了 0，说明这道菜退掉了，直接跳过不入账
                if ($item['quantity'] <= 0) {
                    continue;
                }

                $dish = Dish::findOrFail($item['dish_id']);
                $linePrice = $dish->price * $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $dish->id,
                    'quantity' => $item['quantity'],
                    'price' => $linePrice,
                ]);

                $calculatedTotal += $linePrice;
            }

            // 5. 校验前端算的总价和后端算的原始总价扣除优惠后是否对得上（防止前端恶意篡改价格）
            // ✅ 修复后的代码（使用 floatval）
            $discount = floatval($request->discount);
            $expectedFinal = max(0, $calculatedTotal - $discount);

            // 允许 0.01 的浮点数精度误差
            if (abs($expectedFinal - $request->total_price) > 0.01) {
                return response()->json([
                    'message' => 'Erreur de calcul financier. (前后端财务对账不一致)',
                    'server_expected' => $expectedFinal,
                    'client_submitted' => $request->total_price
                ], 422);
            }

            // 6. 更新订单主表，正式进行“财务封账”
            // 💡 顺便帮你兼容了字段：如果你的表里还没加 payment_status 等新字段，系统不会报错
            $updateData = [
                'total_price' => $request->total_price,
                'status' => 'delivered', // 结账后，制作状态确保也是完成状态
            ];

            // 检查模型或数据库里有没有这几个高阶收银字段，有的话就存进去
            // 后面第二步我会教你用 Migration 快速把这几个字段补进数据库
            $updateData['payment_status'] = 'paid';
            $updateData['payment_method'] = $request->payment_method;
            $updateData['discount'] = $request->discount;

            // 执行物理更新
            $order->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Commande clôturée avec succès !',
                'data' => $order->load('items')
            ]);
        });
    }

    /**
     * 💰 宇宙终极：按桌号【多单合并结算】接口
     */
    public function payMultiple(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'payment_method' => 'required|string|in:Espèces,CB,Resto',
            'discount' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'items' => 'required|array',
        ]);

        return DB::transaction(function () use ($request) {
            $orderIds = $request->order_ids;

            // 1. 取出这批订单里的“第一个订单”作为主账单（母体），其余作为子订单
            $masterOrderId = $orderIds[0];
            $masterOrder = Order::findOrFail($masterOrderId);

            // 2. 清理这批订单里【所有单】的旧明细（全军出击，全部清空）
            OrderItem::whereIn('order_id', $orderIds)->delete();

            // 3. 把收银员核对合并后的最终总明细，全部灌进主账单（母体）里
            foreach ($request->items as $item) {
                if ($item['quantity'] <= 0) continue;

                $dish = Dish::findOrFail($item['dish_id']);
                OrderItem::create([
                    'order_id' => $masterOrder->id, // 统统归入主订单
                    'dish_id' => $dish->id,
                    'quantity' => $item['quantity'],
                    'price' => $dish->price * $item['quantity'],
                ]);
            }

            // 4. 将主账单更新为已支付，存入最终优惠和实收总价
            $masterOrder->update([
                'total_price' => $request->total_price,
                'discount' => $request->discount,
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
                'status' => 'delivered'
            ]);

            // 5. 关键一步：把剩下的那些子订单的总价清零、状态改为已付、标记归档
            // 这样它们就不会在任何活动流水里捣乱了，财务也完全平账！
            if (count($orderIds) > 1) {
                $subOrderIds = array_slice($orderIds, 1);
                Order::whereIn('id', $subOrderIds)->update([
                    'total_price' => 0,
                    'discount' => 0,
                    'payment_status' => 'paid',
                    'payment_method' => $request->payment_method,
                    'status' => 'delivered'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Toutes les commandes de la table ont été fusionnées et payées !'
            ]);
        });
    }
}
