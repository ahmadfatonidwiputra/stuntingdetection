<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orang_tua_anak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orang_tua_id')->constrained('orang_tua_profile')->onDelete('cascade');
            $table->foreignId('anak_id')->constrained('anak')->onDelete('cascade');
            $table->enum('hubungan', ['ayah', 'ibu', 'wali'])->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['orang_tua_id', 'anak_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tua_anak');
    }
};
