<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DishController;

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
