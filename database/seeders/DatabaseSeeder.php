<?php

namespace Database\Seeders;

use App\Models\Measurement;
use App\Models\PetugasProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Super Admin ────────────────────────────
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@aistuntdetect.app',
            'role' => 'super_admin',
            'status' => 'active',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // ── Active Petugas (with sample data) ──────
        $petugasActive = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@posyandu.com',
            'role' => 'petugas_posyandu',
            'status' => 'active',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        PetugasProfile::create([
            'user_id' => $petugasActive->id,
            'nama_lengkap' => 'Siti Nurhaliza',
            'nik' => '3201012345670001',
            'no_telepon' => '081234567890',
            'posyandu_name' => 'Posyandu Melati',
            'posyandu_address' => 'Jl. Merdeka No. 10',
            'kelurahan' => 'Sukamaju',
            'kecamatan' => 'Cilandak',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        // Create sample measurements for active petugas
        $birthDate = now()->subMonths(14);
        for ($i = 0; $i < 5; $i++) {
            $measuredAt = now()->subDays(30 * (4 - $i));
            $ageMonths = 14 - (4 - $i);
            $baseHeight = 69.0 + ($i * 1.0);
            $zScore = Measurement::calculateZScore($baseHeight, $ageMonths, 'L');

            Measurement::create([
                'user_id' => $petugasActive->id,
                'child_name' => 'Budi Santoso',
                'parent_name' => 'Bapak Joko',
                'address' => 'Jl. Merdeka No 1',
                'posyandu_name' => 'Posyandu Melati',
                'birth_date' => $birthDate,
                'gender' => 'L',
                'height_cm' => $baseHeight,
                'weight_kg' => 8.5 + ($i * 0.3),
                'z_score' => $zScore,
                'stunting_category' => Measurement::getStuntingCategory($zScore),
                'measured_at' => $measuredAt,
                'notes' => 'Tumbuh kembang dipantau rutin',
            ]);
        }

        // ── Pending Petugas ────────────────────────
        $petugasPending = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@posyandu.com',
            'role' => 'petugas_posyandu',
            'status' => 'pending',
            'password' => bcrypt('password'),
        ]);

        PetugasProfile::create([
            'user_id' => $petugasPending->id,
            'nama_lengkap' => 'Dewi Lestari',
            'nik' => '3201012345670002',
            'no_telepon' => '081234567891',
            'posyandu_name' => 'Posyandu Mawar',
            'posyandu_address' => 'Jl. Kenanga No. 5',
            'kelurahan' => 'Cipete',
            'kecamatan' => 'Cilandak',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
        ]);

        // ── Rejected Petugas ───────────────────────
        $petugasRejected = User::create([
            'name' => 'Rina Susanti',
            'email' => 'rina@posyandu.com',
            'role' => 'petugas_posyandu',
            'status' => 'rejected',
            'password' => bcrypt('password'),
        ]);

        PetugasProfile::create([
            'user_id' => $petugasRejected->id,
            'nama_lengkap' => 'Rina Susanti',
            'nik' => '3201012345670003',
            'no_telepon' => '081234567892',
            'posyandu_name' => 'Posyandu Dahlia',
            'posyandu_address' => 'Jl. Anggrek No. 3',
            'kelurahan' => 'Lebak Bulus',
            'kecamatan' => 'Cilandak',
            'kota' => 'Jakarta Selatan',
            'provinsi' => 'DKI Jakarta',
            'rejection_reason' => 'Dokumen Surat Tugas tidak lengkap. Mohon lampirkan SK Pengangkatan dari Kelurahan.',
        ]);
    }
}
