<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DishController extends Controller
{
    /**
     * 1. 菜品列表 API
     */
    public function index()
    {
        return response()->json(Dish::orderBy('id', 'desc')->get());
    }

    /**
     * 2. 创建新菜品（带图片上传功能 📸）
     */
    public function store(Request $request)
    {
        // 1. 严格验证表单
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // 🎯 核心防爆盾：捕获数据库所有的不合规行为（例如漏掉必填字段或模型被锁）
        try {
            $imageUrl = null;

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('dishes', 'public');
                $imageUrl = Storage::url($path);
            }

            // 写入数据库
            $dish = Dish::create([
                'name' => $request->name,
                'price' => $request->price,
                'image_url' => $imageUrl ?? '', // 确保不是 null，防止迁移未生效报错
                'restaurant_id' => 1, // 临时写死，后续会改成动态选择餐厅
            ]);

            return response()->json([
                'success' => true,
                'message' => '菜品添加成功！',
                'data' => $dish
            ], 201);
        } catch (\Exception $e) {
            // 🌟 核心拦截：如果崩了，绝不抛出庞大的 HTML 网页，而是直接把纯文本错误原因喂给前端！
            return response()->json([
                'success' => false,
                'error_message' => 'Laravel 数据库报错: ' . $e->getMessage()
            ], 422); // 使用 422 状态码让前端走 catch 逻辑
        }
    }

    /**
     * 3. 删除菜品
     */
    public function destroy(int $id)
    {
        $dish = Dish::findOrFail($id);

        // 如果有图片，顺便把硬盘里的图片文件删掉，省空间
        if ($dish->image_url) {
            $oldPath = str_replace('/storage/', '', $dish->image_url);
            Storage::disk('public')->delete($oldPath);
        }

        $dish->delete();

        return response()->json([
            'success' => true,
            'message' => '菜品已成功下架！'
        ]);
    }

    // 🎯 新增：修改菜品接口
    public function update(Request $request, int $id)
    {
        // 1. 验证提交的数据
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 新图片可选
        ]);

        try {
            $dish = Dish::findOrFail($id);
            $imageUrl = $dish->image_url; // 默认保留原图片路径

            // 2. 如果用户上传 enemy 新图片，则替换旧图
            if ($request->hasFile('image')) {
                // 可选：如果原来有旧图，可以先从存储里删掉旧图，防止占用服务器空间
                if ($dish->image_url) {
                    $oldPath = str_replace('/storage/', '', $dish->image_url);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }

                // 存储新图片
                $path = $request->file('image')->store('dishes', 'public');
                $imageUrl = \Illuminate\Support\Facades\Storage::url($path);
            }

            // 3. 更新数据库记录
            $dish->update([
                'name' => $request->name,
                'price' => $request->price,
                'image_url' => $imageUrl,
                'restaurant_id' => 1, // 确保强绑定
            ]);

            return response()->json([
                'success' => true,
                'message' => '菜品修改成功！',
                'data' => $dish
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error_message' => 'Laravel 修改失败: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * 🍔 一键切换菜品售罄/有货状态
     * 对应路由：PATCH /api/dishes/{id}/toggle-available
     */
    public function toggleAvailable(int $id)
    {
        $dish = \App\Models\Dish::findOrFail($id);

        // 状态取反（true 变 false，false 变 true）
        $dish->is_available = !$dish->is_available;
        $dish->save();

        return response()->json([
            'success' => true,
            'is_available' => $dish->is_available,
            'message' => $dish->is_available ? '菜品已恢复供应！' : '菜品已成功估清下架！'
        ]);
    }
}
