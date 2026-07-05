<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('daily_plans')->cascadeOnDelete();
            $table->decimal('fact_qty', 10, 2);
            $table->decimal('overtime', 10, 2)->default(0);
            $table->string('crew_type')->nullable();
            $table->text('comment')->nullable();
            $table->string('status')->default('PENDING_SC');
            $table->string('local_id')->nullable()->unique(); // Offline Sync Idempotency
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_facts');
    }
};
