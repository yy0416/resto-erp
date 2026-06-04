<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            // 🌟 在 name 字段后面，追加一个允许为空（nullable）的 image_url 字段
            $table->string('image_url')->nullable()->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            // 如果回滚迁移，就删掉这个字段
            $table->dropColumn('image_url');
        });
    }
};
