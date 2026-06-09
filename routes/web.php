<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\TableSettingController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| 📱 前端多端点单入口 (Front-end Apps)
|--------------------------------------------------------------------------
| 这些是面向客人的界面，不需要登录后台账号，保持公开访问
*/

// 1. 店内固定平板端

// 📱 完美配合你的前端动态抓取
Route::get('/order-tablette', function (Request $request) {
    // 平板页面不需要在后端强行 compact，因为它自己在 JS 里面会用 window.location.search 去抓！
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
*/
Route::middleware(['auth'])->group(function () {

    // ⚙️ 通用后台主页（管理员与员工均能查看，但前端菜单根据角色显示不同按钮）
    Route::get('/admin', function () {
        // 📡 1. 在这里亲手把配置好的桌子按桌号升序捞出来
        $tables = \App\Models\Table::orderBy('table_number', 'asc')->get();

        // 📦 2. 通过 compact('tables') 顺手把数据送给 admin 视图
        return view('admin', compact('tables'));
    });

    // 🚪 退出登录注销接口
    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | 👑 仅限老板（Admin）才能操作的硬核基建区
    |--------------------------------------------------------------------------
    */
    Route::middleware([\App\Http\Middleware\IsAdmin::class])->group(function () {
        Route::get('/tables-setting', [TableSettingController::class, 'index'])->name('admin.tables.index');
        Route::post('/tables-setting', [TableSettingController::class, 'store'])->name('admin.tables.store');
        Route::post('/tables-setting/batch', [TableSettingController::class, 'batchGenerate'])->name('admin.tables.batch');
        Route::delete('/tables-setting/{id}', [TableSettingController::class, 'destroy'])->name('admin.tables.destroy');
        Route::put('/tables-setting/{id}', [TableSettingController::class, 'update'])->name('admin.tables.update');
    });
});
