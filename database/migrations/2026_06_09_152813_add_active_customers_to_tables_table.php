<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $blue) {
            // 🎯 新增：当前实际就餐人数，默认为 0
            $blue->integer('active_customers')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $blue) {
            $blue->dropColumn('active_customers');
        });
    }
};
