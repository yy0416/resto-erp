<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DishController;
use App\Http\Controllers\Api\ReportController;


Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::post('/restaurants', [RestaurantController::class, 'store']);

Route::get('/dishes', [DishController::class, 'index']);
Route::post('/dishes', [DishController::class, 'store']);
Route::get('/dishes/{id}', [DishController::class, 'show']);
Route::put('/dishes/{id}', [DishController::class, 'update']);
Route::delete('/dishes/{id}', [DishController::class, 'destroy']);

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);

Route::patch('/orders/{id}', [OrderController::class, 'update']);
Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

Route::get('/customers', [CustomerController::class, 'index']);
Route::post('/customers', [CustomerController::class, 'store']);
Route::get('/customers/{id}', [CustomerController::class, 'show']);
Route::get('/customers/{id}/orders', [CustomerController::class, 'orders']);

// 🎯 新增：收银结账封账接口
// 🎯 新增：收银结账封账接口
// ✅ 修复后的标准代码
Route::post('orders/{id}/pay', [OrderController::class, 'pay']);

// 🎯 新增：多订单合并收银接口
Route::post('orders/pay-multiple', [OrderController::class, 'payMultiple']);


// 🎯 新增：老板财务报表接口
Route::get('reports/dashboard', [ReportController::class, 'index']);
Route::get('reports/history', [ReportController::class, 'history']); // 👈 必须有这一行！
// 🎯 菜品估清控制路由
Route::patch('dishes/{id}/toggle-available', [DishController::class, 'toggleAvailable']);
