<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    // 允许批量写入的字段
    protected $fillable = [
        'name',
        'city',
        'address',
        'phone',
        'email',
    ];
}
