<?php

namespace Database\Seeders;

use App\Models\Measurement;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_superadmin' => true,
            'password' => bcrypt('password123'),
        ]);

        $birthDate = now()->subMonths(14); // Anak berumur 14 bulan saat ini

        // Buat history pengukuran 5 bulan terakhir
        for ($i = 0; $i < 5; $i++) {
            $measuredAt = now()->subDays(30 * (4 - $i));
            $ageMonths = 14 - (4 - $i); // dari umur 10 sampai 14 bulan

            // Generate tinggi badan di bawah rata-rata (Stunting simulation)
            // Median umur 10 bln = 73.3 cm. Kita buat ~69 cm (-2.x SD)
            $baseHeight = 69.0 + ($i * 1.0);
            $zScore = Measurement::calculateZScore($baseHeight, $ageMonths, 'L');

            Measurement::create([
                'user_id' => $user->id,
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
    }
}
