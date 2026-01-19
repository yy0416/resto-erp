<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //按电话搜索
    public function index(Request $request)
    {
        $query = \App\Models\Customer::query();

        if ($request->has('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        $customers = $query->get();

        return response()->json($customers);
    }

    //创建客户
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
            'notes' => 'nullable|string|max:1000',
        ]);
        return \App\Models\Customer::create($validated);
    }

    //显示客户详情
    public function show($id)
    {
        $customer = \App\Models\Customer::findOrFail($id);
        return response()->json($customer);
    }

    //获取客户订单
    public function orders($id)
    {
        $customer = \App\Models\Customer::findOrFail($id);
        $orders = $customer->orders()->with('items')->get();
        return response()->json($orders);
    }
}
