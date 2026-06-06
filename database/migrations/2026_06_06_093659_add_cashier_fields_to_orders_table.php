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
        Schema::table('orders', function (Blueprint $table) {
            // 🎯 新增：支付状态（默认未付 unpaid，付完变 paid）
            $table->string('payment_status')->default('unpaid')->after('status');

            // 🎯 新增：付款渠道（Espèces, CB, Resto，结账前为空）
            $table->string('payment_method')->nullable()->after('payment_status');

            // 🎯 新增：优惠金额（默认 0.00）
            $table->decimal('discount', 10, 2)->default(0.00)->after('total_price');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'payment_method', 'discount']);
        });
    }
};
