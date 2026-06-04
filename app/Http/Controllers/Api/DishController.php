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
        // 严格的表单验证
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 最大2MB
        ]);

        $imageUrl = null;

        // 如果管理员上传了图片，把它存进 local 的 public 磁盘
        if ($request->hasFile('image')) {
            // 存储到 storage/app/public/dishes 目录下
            $path = $request->file('image')->store('dishes', 'public');
            // 生成前端可以直接访问的 URL 路径
            $imageUrl = Storage::url($path);
        }

        // 写入数据库
        $dish = Dish::create([
            'name' => $request->name,
            'price' => $request->price,
            'image_url' => $imageUrl, // 💡 确保你的 dishes 表里有这个字段，没有的话可以先不传
        ]);

        return response()->json([
            'success' => true,
            'message' => '菜品添加成功！',
            'data' => $dish
        ], 201);
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
}
