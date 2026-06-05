<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 📱 前端多端点单入口 (Front-end Apps)
|--------------------------------------------------------------------------
*/

// 1. 店内固定平板端（改成与你代码内部逻辑一致的 /order-tablette）
Route::get('/order-tablette', function () {
    return view('order-tablette');
});

// 2. 店内打包自助机端
Route::get('/order-borne', function () {
    return view('order-borne');
});

// 3. 远程网页线上的Click & Collect
Route::get('/order-web', function () {
    return view('order-web');
});


/*
|--------------------------------------------------------------------------
| ⚙️ 管理员总控制后台 (Central Admin Dashboard SPA)
|--------------------------------------------------------------------------
*/

// 统一的总控制台入口 (无刷新单页大后台)
Route::get('/admin', function () {
    return view('admin');
});
