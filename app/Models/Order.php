<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'restaurant_id',
        'total_price',
        'status',
        'order_type',
        'table_number',
        'started_at'
    ];
    protected $casts = [
        'total_price' => 'float',
        'started_at' => 'datetime',
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
            'pending' => ['paid', 'preparing', 'delivered', 'cancelled'],
            'paid' => ['preparing', 'delivered', 'cancelled'],
            'preparing' => ['delivered', 'cancelled'],
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
