<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $fillable = ['customer_id', 'restaurant_id', 'total_price', 'status'];
    protected $casts = [
        'total_price' => 'float',
    ];



    public const STATUS_PENDING   = 'pending';
    public const STATUS_PAID      = 'paid';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    // Order.php
    public static function canChangeStatus(string $from, string $to): bool
    {
        $transitions = [
            'pending' => ['paid', 'cancelled'],
            'paid' => ['preparing', 'cancelled'],
            'preparing' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        return in_array($to, $transitions[$from] ?? []);
    }



    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
