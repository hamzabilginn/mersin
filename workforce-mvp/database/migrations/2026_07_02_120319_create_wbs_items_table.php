<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wbs_items', function (Blueprint $table) {
            $table->string('zzz_code')->primary();
            $table->string('tow')->nullable();
            $table->string('stow')->nullable();
            $table->string('sstow')->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wbs_items');
    }
};
