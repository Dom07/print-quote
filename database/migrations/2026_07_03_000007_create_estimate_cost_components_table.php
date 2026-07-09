<?php

use App\Enums\RateType;
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
        Schema::create('estimate_cost_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('unit')->nullable();
            $table->decimal('rate', 12, 4)->nullable();
            $table->decimal('original_rate', 12, 4)->nullable();
            $table->string('rate_type')->default(RateType::PerSheet->value);
            $table->boolean('is_overridden')->default(false);
            $table->text('calculation_note')->nullable();
            $table->text('override_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimate_cost_components');
    }
};
