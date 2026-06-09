<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * 📊 1. 老板仪表盘核心数据接口 (卡片汇总与爆款排行)
     * 对应路由：GET /api/reports/dashboard
     */
    /**
     * 📊 1. 老板仪表盘核心数据接口 (全面升级为 whereDate 防时区干扰版)
     * 对应路由：GET /api/reports/dashboard
     */
    public function index(Request $request)
    {
        // 拿到前端传过来的日期字符串，比如 "2026-06-08"
        $dateStr = $request->query('date', Carbon::today()->toDateString());

        // 🎯 核心防线升级：基础查询改用 whereDate，直接穿透年月日，彻底解决时区卡死导致的 0 营业额问题！
        $todayPaidQuery = Order::where('payment_status', 'paid')
            ->whereDate('started_at', $dateStr);

        // 1. 营业额与优惠汇总
        $financials = (clone $todayPaidQuery)->select(
            DB::raw('SUM(total_price) as total_revenue'),
            DB::raw('SUM(discount) as total_discount'),
            DB::raw('COUNT(id) as total_orders')
        )->first();

        // 2. 支付渠道分布统计 (Espèces, CB, Resto)
        $paymentMethods = (clone $todayPaidQuery)->select(
            'payment_method',
            DB::raw('SUM(total_price) as amount'),
            DB::raw('COUNT(id) as count')
        )->groupBy('payment_method')->get();

        // 3. 爆款菜品排行榜 (Top 5)
        $topDishes = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.payment_status', 'paid')
            ->whereDate('orders.started_at', $dateStr) // 👈 这里同步改为 whereDate
            ->select(
                'dishes.name as dish_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_sales') // 🎯 修正：总销售额应该是 单价 * 数量
            )
            ->groupBy('dishes.id', 'dishes.name')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return response()->json([
            'success' => true,
            'date' => $dateStr,
            'summary' => [
                'revenue' => (float)($financials->total_revenue ?? 0),
                'discount' => (float)($financials->total_discount ?? 0),
                'orders_count' => (int)($financials->total_orders ?? 0),
            ],
            'payment_distribution' => $paymentMethods,
            'top_dishes' => $topDishes
        ]);
    }

    /**
     * 📜 2. 补上前端最需要的：历史账单明细流水接口！
     * 对应路由：GET /api/reports/history
     */
    public function history(Request $request)
    {
        // 拿到前端传过来的日期字符串，比如 "2026-06-06"
        $dateStr = $request->query('date', Carbon::today()->toDateString());

        // 🎯 降维打击：直接比对数据库里的年月日，把今天所有的已付订单、连同它们吃了啥（items.dish）全部捞出来！
        $historyOrders = Order::with('items.dish')
            ->where('payment_status', 'paid')
            ->whereDate('started_at', $dateStr)
            ->orderByDesc('started_at')
            ->get();

        return response()->json([
            'success' => true,
            'date' => $dateStr,
            'data' => $historyOrders
        ]);
    }
}
