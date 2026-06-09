<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DishController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| 🔓 公共与员工（Staff）通用 API 路由集群
|--------------------------------------------------------------------------
| 平板点餐端、收银员日常点单、估清、结账都在这里通行
*/

// 🛒 餐厅与顾客基础信息
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::post('/restaurants', [RestaurantController::class, 'store']); // 如果未来需要改权限再移走

Route::get('/customers', [CustomerController::class, 'index']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers/{id}', [CustomerController::class, 'show']);
Route::get('/customers/{id}/orders', [CustomerController::class, 'orders']);

// 🍔 菜品读取与快速控制
Route::get('/dishes', [DishController::class, 'index']);                    // 平板拿菜单
Route::get('/dishes/{id}', [DishController::class, 'show']);                 // 查看单道菜
Route::patch('dishes/{id}/toggle-available', [DishController::class, 'toggleAvailable']); // 🎯 核心：收银一键估清开关

// 📝 订单流水线（点单、更新、结账）
Route::post('/orders', [OrderController::class, 'store']);                    // 平板下单
Route::get('/orders', [OrderController::class, 'index']);                     // 跑堂查看订单
Route::get('/orders/{id}', [OrderController::class, 'show']);                 // 订单详情
Route::patch('/orders/{id}', [OrderController::class, 'update']);             // 厨政/跑堂更新订单状态
Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);       // 取消订单
Route::post('orders/{id}/pay', [OrderController::class, 'pay']);             // 收银：单桌结账
Route::post('orders/pay-multiple', [OrderController::class, 'payMultiple']); // 收银：合并结账



/*
|--------------------------------------------------------------------------
| 👑 老板（Admin）尊享机密 API 路由集群
|--------------------------------------------------------------------------
| 使用 web 共享后台登录 Session，auth 确保已登录，IsAdmin 确保是管理员身份
*/
Route::middleware(['web', 'auth', \App\Http\Middleware\IsAdmin::class])->group(function () {

    // 📊 财务大厅数据报表 (完美接通 Session，403 终结者)
    Route::get('reports/dashboard', [ReportController::class, 'index']);
    Route::get('reports/history', [ReportController::class, 'history']);

    // 🛠️ 核心菜单档案管理（增、改、删）
    Route::post('/dishes', [DishController::class, 'store']);          // 上架新菜
    Route::put('/dishes/{id}', [DishController::class, 'update']);     // ✏️ 修改菜品单价/名字
    Route::delete('/dishes/{id}', [DishController::class, 'destroy']); // 🗑️ 彻底销毁下架菜品

});
