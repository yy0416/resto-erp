<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'table_number' => $this->table_number,
            'order_type' => $this->order_type,
            'status' => $this->status,
            'total_price' => (float) $this->total_price, // 确保有总价
            'started_at' => $this->started_at,
            // 🌟 核心：必须把 items 塞进去，并允许它携带预加载的 dish
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'dish_id' => $item->dish_id,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    // 🌟 关键：把菜品的原始名字和单价带上，供平板端计算
                    'dish' => $item->dish ? [
                        'name' => $item->dish->name,
                        'price' => (float) $item->dish->price,
                    ] : null,
                ];
            }),
        ];
    }
}
