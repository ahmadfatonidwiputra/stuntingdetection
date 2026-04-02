<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan posyandu_id ke petugas_profiles
        Schema::table('petugas_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('posyandu_id')->nullable()->after('user_id');
            $table->foreign('posyandu_id')->references('id')->on('posyandu')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('petugas_profiles', function (Blueprint $table) {
            $table->dropForeign(['posyandu_id']);
            $table->dropColumn('posyandu_id');
        });
    }
};
