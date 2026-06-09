<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Table;

class TableSettingController extends Controller
{
    /**
     * 🏰 1. 桌位管理主页
     */
    public function index()
    {
        $tables = Table::orderBy('id', 'asc')->get();
        return view('admin.tables-setting', compact('tables'));
    }

    /**
     * ➕ 2. 快捷单个创建桌子
     */
    public function store(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string|unique:tables,table_number',
            'seats_count' => 'required|integer|min:1'
        ]);

        Table::create([
            'table_number' => $request->table_number,
            'seats_count' => $request->seats_count,
            'status' => 'empty'
        ]);

        return redirect()->back()->with('success', 'Table créée avec succès ! (桌位创建成功)');
    }

    /**
     * ⚡ 3. 极速一键批量生成桌子 (开店神器)
     */
    public function batchGenerate(Request $request)
    {
        $request->validate([
            'prefix' => 'nullable|string|max:5', // 比如加前缀 "V" 代表包厢
            'start_num' => 'required|integer|min:1',
            'end_num' => 'required|integer|gte:start_num',
            'default_seats' => 'required|integer|min:1'
        ]);

        $prefix = $request->input('prefix', '');
        $start = (int)$request->start_num;
        $end = (int)$request->end_num;
        $seats = (int)$request->default_seats;

        $createdCount = 0;

        for ($i = $start; $i <= $end; $i++) {
            $tableNum = $prefix . $i;

            // 检查是否已经存在，存在就跳过，防止报错中断
            if (!Table::where('table_number', $tableNum)->exists()) {
                Table::create([
                    'table_number' => $tableNum,
                    'seats_count' => $seats,
                    'status' => 'empty'
                ]);
                $createdCount++;
            }
        }

        return redirect()->back()->with('success', "Génération réussie : {$createdCount} tables créées ! (成功批量生成 {$createdCount} 张桌子)");
    }

    /**
     * 🗑️ 4. 单个删除桌子
     */
    public function destroy(int $id)
    {
        $table = Table::findOrFail($id);

        if ($table->status === 'dining') {
            return redirect()->back()->with('error', 'Impossible de supprimer une table en cours d\'utilisation ! (有人正在用餐的桌子不能删！)');
        }

        $table->delete();
        return redirect()->back()->with('success', 'Table supprimée ! (桌位已成功拆除)');
    }

    // 🎯 新增：允许老板在基建后台随时修改座位数或桌号
    public function update(Request $request, int $id)
    {
        $table = Table::findOrFail($id);

        // 验证规则：桌号必须有，座位数必须是正整数
        $request->validate([
            'table_number' => 'required|string|max:50|unique:tables,table_number,' . $id,
            'seats_count' => 'required|integer|min:1',
        ]);

        // 写入新配置
        $table->table_number = $request->table_number;
        $table->seats_count = $request->seats_count;
        $table->save();

        // 返回成功通知，顺便原路弹回
        return back()->with('success', '🪑 Table mise à jour ! 桌位基础配置已成功修改！');
    }
}
