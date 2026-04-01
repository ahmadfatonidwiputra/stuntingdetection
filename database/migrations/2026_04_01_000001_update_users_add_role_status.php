<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('petugas_posyandu')->after('email');
            $table->string('status')->default('pending')->after('role');
        });

        // Migrate existing data: is_superadmin = true → role = super_admin, status = active
        DB::table('users')->where('is_superadmin', true)->update([
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        // Set all non-superadmin users to active too (existing users)
        DB::table('users')->whereNull('is_superadmin')->update([
            'role' => 'petugas_posyandu',
            'status' => 'active',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_is_superadmin_unique');
            $table->dropColumn('is_superadmin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_superadmin')->nullable()->after('email')->unique();
        });

        DB::table('users')->where('role', 'super_admin')->update([
            'is_superadmin' => true,
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status']);
        });
    }
};
