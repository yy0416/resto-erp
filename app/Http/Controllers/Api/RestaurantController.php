<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRestaurantRequest;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Restaurant::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRestaurantRequest $request)
    {
        return Restaurant::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(Restaurant $restaurant)
    {
        return $restaurant;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRestaurantRequest $request, Restaurant $restaurant)
    {
        $restaurant->update($request->validated());
        return $restaurant;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return response()->noContent();
    }

    public function updateActiveCustomers(Request $request, int $id)
    {
        $table = Table::findOrFail($id);

        // 验证输入的就餐人数
        $request->validate([
            'active_customers' => 'required|integer|min:0'
        ]);

        $customers = intval($request->active_customers);
        $table->active_customers = $customers;

        // 💡 智能联动逻辑：如果输入的实际就餐人数大于 0，桌子状态立刻自动切换为 'occupied' (用餐中)
        // 如果人数改成了 0，说明客人全部买单或清空了，状态自动恢复为 'empty' (空闲)
        if ($customers > 0) {
            $table->status = 'occupied';
        } else {
            $table->status = 'empty';
        }

        $table->save();

        return response()->json([
            'success' => true,
            'message' => 'Table mise à jour avec succès (桌位客座人数及状态已同步更新)',
            'table' => $table
        ]);
    }
}
