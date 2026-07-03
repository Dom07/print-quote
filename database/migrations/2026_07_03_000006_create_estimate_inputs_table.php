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
        Schema::create('estimate_inputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('product_type')->nullable();
            $table->unsignedInteger('pieces_per_sheet')->nullable();
            $table->unsignedInteger('production_sheets')->nullable();
            $table->unsignedInteger('wastage_sheets')->nullable();
            $table->unsignedInteger('total_sheets_with_wastage')->nullable();
            $table->json('raw_inputs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_inputs');
    }
};
