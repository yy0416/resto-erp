<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('dishes', function (Blueprint $table) {
            // 🎯 新增 is_available 字段，默认为 true（有货）
            $table->boolean('is_available')->default(true)->after('price');
        });
    }

    public function down()
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('is_available');
        });
    }
};
