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
        Schema::table('measurements', function (Blueprint $table) {
            $table->decimal('manual_height_cm', 5, 2)->nullable()->after('weight_kg');
            $table->decimal('manual_weight_kg', 5, 2)->nullable()->after('manual_height_cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropColumn(['manual_height_cm', 'manual_weight_kg']);
        });
    }
};
