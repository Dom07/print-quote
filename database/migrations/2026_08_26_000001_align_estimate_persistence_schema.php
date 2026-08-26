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
        if (! Schema::hasColumn('estimates', 'quote_number')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->string('quote_number')->nullable()->unique();
            });
        }

        if (! Schema::hasColumn('estimates', 'quoted_at')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->timestamp('quoted_at')->nullable();
            });
        }

        if (Schema::hasColumn('estimates', 'customer_id')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->dropConstrainedForeignId('customer_id');
            });
        }

        if (Schema::hasColumn('estimates', 'created_by')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasColumn('estimate_cost_components', 'pricing_item_id')) {
            Schema::table('estimate_cost_components', function (Blueprint $table) {
                $table->dropConstrainedForeignId('pricing_item_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('estimate_cost_components', 'pricing_item_id')) {
            Schema::table('estimate_cost_components', function (Blueprint $table) {
                $table->foreignId('pricing_item_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('estimates', 'created_by')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('estimates', 'customer_id')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasColumn('estimates', 'quoted_at')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->dropColumn('quoted_at');
            });
        }

        if (Schema::hasColumn('estimates', 'quote_number')) {
            Schema::table('estimates', function (Blueprint $table) {
                $table->dropUnique(['quote_number']);
                $table->dropColumn('quote_number');
            });
        }
    }
};
