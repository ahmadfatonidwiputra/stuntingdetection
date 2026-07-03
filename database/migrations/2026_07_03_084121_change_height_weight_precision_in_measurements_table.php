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
            $table->decimal('height_cm', 5, 2)->nullable()->change();
            $table->decimal('weight_kg', 5, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->decimal('height_cm', 5, 1)->nullable()->change();
            $table->decimal('weight_kg', 5, 1)->nullable()->change();
        });
    }
};
