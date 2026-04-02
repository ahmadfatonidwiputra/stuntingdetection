<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->unsignedBigInteger('anak_id')->nullable()->after('user_id');
            $table->foreign('anak_id')->references('id')->on('anak')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropForeign(['anak_id']);
            $table->dropColumn('anak_id');
        });
    }
};
