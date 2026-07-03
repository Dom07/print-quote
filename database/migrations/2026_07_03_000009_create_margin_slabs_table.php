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
        Schema::create('margin_slabs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_amount', 14, 4)->nullable();
            $table->decimal('max_amount', 14, 4)->nullable();
            $table->decimal('margin_percentage', 8, 4);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('margin_slabs');
    }
};
