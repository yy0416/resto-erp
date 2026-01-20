<?php

use Illuminate\Support\Facades\Route;

Route::get('/order', function () {
    return view('order');
});
