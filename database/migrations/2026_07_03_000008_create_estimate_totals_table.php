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
        Schema::create('estimate_totals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('cost_per_sheet', 14, 4)->nullable();
            $table->decimal('cost_per_piece', 14, 4)->nullable();
            $table->decimal('subtotal', 14, 4)->nullable();
            $table->decimal('margin_percentage', 8, 4)->nullable();
            $table->decimal('margin_amount', 14, 4)->nullable();
            $table->decimal('grand_total', 14, 4)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_totals');
    }
};
