<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            // 桌号：用 string 是为了支持 "V1"(包厢), "T1"(露天) 这种个性化桌号
            $table->string('table_number')->unique();
            // 容纳人数：默认 4 人桌
            $table->integer('seats_count')->default(4);
            // 实时状态：empty(空闲), dining(用餐中), reserved(已预订)
            $table->string('status')->default('empty');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
