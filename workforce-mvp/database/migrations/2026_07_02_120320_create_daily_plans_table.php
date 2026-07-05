<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_plans', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('kkk');
            $table->string('zzz_code');
            $table->decimal('planned_qty', 10, 2);
            $table->integer('planned_manday');
            $table->string('assigned_hom');
            $table->string('status')->default('ASSIGNED');
            $table->timestamps();
            
            $table->foreign('zzz_code')->references('zzz_code')->on('wbs_items');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_plans');
    }
};
