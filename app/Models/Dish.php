<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    // 🌟 允许被公开批量写入的字段白名单
    protected $fillable = [
        'name',
        'description',
        'price',
        'restaurant_id',
        'image_url', // 🎯 必须加上这一行！
    ];
}
