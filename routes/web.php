<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| 📱 前端多端点单入口 (Front-end Apps)
|--------------------------------------------------------------------------
| 这些是面向客人的界面，不需要登录后台账号，保持公开访问
*/

// 1. 店内固定平板端
Route::get('/order-tablette', function () {
    return view('order-tablette');
});

// 2. 店内打包自助机端
Route::get('/order-borne', function () {
    return view('order-borne');
});

// 3. 远程网页线上的 Click & Collect
Route::get('/order-web', function () {
    return view('order-web');
});


/*
|--------------------------------------------------------------------------
| 🔑 开放验证通道 (Authentication)
|--------------------------------------------------------------------------
*/

// 🔓 所有人（游客）都能访问的登录页面与提交接口
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| 🔒 必须登录后才能访问的“全封闭保护区” (Protected Zones)
|--------------------------------------------------------------------------
| 挂上了 'auth' 中间件。任何没登录的请求试图强闯，都会被直接重定向轰回 /login 页面！
*/
Route::middleware(['auth'])->group(function () {

    // ⚙️ 管理员与员工总控制后台 (彻底锁死在这里，安全感拉满)
    Route::get('/admin', function () {
        return view('admin');
    });

    // 🚪 退出登录注销接口
    Route::post('/logout', [AuthController::class, 'logout']);
});
