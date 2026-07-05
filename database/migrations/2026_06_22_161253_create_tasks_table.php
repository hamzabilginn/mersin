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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('zzz_code');
            $table->string('tow');
            $table->string('stow');
            $table->string('sstow');
            
            $table->decimal('planned_qty', 8, 2)->default(0);
            $table->decimal('planned_man_day', 8, 2)->default(0);
            $table->decimal('fact_qty', 8, 2)->nullable();
            $table->decimal('fact_man_day', 8, 2)->nullable();
            $table->decimal('overtime', 8, 2)->nullable();
            
            $table->text('comment')->nullable();
            $table->string('local_id')->nullable(); // For Idempotency
            
            $table->string('status')->default('draft'); // draft, assigned, in_progress, pending_sc, pending_pm, approved

            $table->foreignId('tech_office_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hom_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('sc_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('pm_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
