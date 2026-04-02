<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anak', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('petugas_id')->nullable();
            $table->foreign('petugas_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('posyandu_id')->nullable();
            $table->foreign('posyandu_id')->references('id')->on('posyandu')->nullOnDelete();

            // Data Identitas
            $table->string('nama', 200);
            $table->string('nik_anak', 16)->unique()->nullable();
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);

            // Data Keluarga
            $table->string('no_kk', 16)->nullable();
            $table->string('nama_ayah', 200)->nullable();
            $table->string('nik_ayah', 16)->nullable();
            $table->string('nama_ibu', 200)->nullable();
            $table->string('nik_ibu', 16)->nullable();
            $table->string('no_telepon_ortu', 15)->nullable();
            $table->string('email_ortu', 100)->nullable();
            $table->text('alamat')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anak');
    }
};
