<?php

use Illuminate\Support\Facades\Route;

// 1. 店内固定平板端
Route::get('/order/tablette', function () {
    return view('order-tablette');
});

// 2. 店内打包自助机端
Route::get('/order/borne', function () {
    return view('order-borne');
});

// 3. 远程网页线上的Click & Collect
Route::get('/order/web', function () {
    return view('order-web');
});

// 4. 管理员后台界面
Route::get('/admin/dishes', function () {
    return view('admin-dishes');
});
