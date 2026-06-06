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
    public function index(Request $request)
    {
        $dateStr = $request->query('date', Carbon::today()->toDateString());

        $todayStart = Carbon::parse($dateStr)->startOfDay();
        $todayEnd = Carbon::parse($dateStr)->endOfDay();

        // 基础查询：选定日期已经付过钱的订单
        $todayPaidQuery = Order::where('payment_status', 'paid')
            ->whereBetween('started_at', [$todayStart, $todayEnd]);

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
            ->whereBetween('orders.started_at', [$todayStart, $todayEnd])
            ->select(
                'dishes.name as dish_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price) as total_sales')
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
