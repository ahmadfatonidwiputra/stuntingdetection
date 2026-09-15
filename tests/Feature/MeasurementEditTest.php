<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Measurement;
use App\Models\PetugasProfile;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasurementEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_edit_form_with_saved_values(): void
    {
        [$petugas, $anak] = $this->createPosyanduContext('petugas-edit@example.com');

        $measurement = $this->createMeasurement($petugas, $anak, [
            'height_cm' => 78.40,
            'weight_kg' => 9.60,
            'manual_height_cm' => 79.00,
            'manual_weight_kg' => 9.80,
            'measured_at' => '2026-03-10 09:30:00',
            'notes' => 'Catatan lama',
        ]);

        $this->actingAs($petugas)
            ->get(route('measurements.edit', $measurement))
            ->assertOk()
            ->assertSeeText('Edit Pengukuran')
            ->assertSee('value="78.40"', false)
            ->assertSee('value="9.60"', false)
            ->assertSee('value="79.00"', false)
            ->assertSee('value="9.80"', false)
            ->assertSee('value="2026-03-10"', false)
            ->assertSee('Catatan lama', false);
    }

    public function test_owner_can_change_date_measurements_and_manual_values(): void
    {
        [$petugas, $anak] = $this->createPosyanduContext('petugas-update@example.com');

        $measurement = $this->createMeasurement($petugas, $anak, [
            'height_cm' => 70.00,
            'weight_kg' => 8.00,
            'z_score' => -1.25,
            'stunting_category' => 'Normal',
            'measured_at' => '2026-03-10 09:30:00',
        ]);

        $response = $this->actingAs($petugas)->put(route('measurements.update', $measurement), [
            'height_cm' => 64.5,
            'weight_kg' => 7.1,
            'manual_height_cm' => 65,
            'manual_weight_kg' => 7.3,
            'measured_at' => '2026-05-20',
            'notes' => 'Koreksi hasil ukur',
        ]);

        $response->assertRedirect(route('measurements.anak.show', $anak->id));
        $response->assertSessionHas('success');

        $measurement->refresh();

        $this->assertSame('64.50', (string) $measurement->height_cm);
        $this->assertSame('7.10', (string) $measurement->weight_kg);
        $this->assertSame('65.00', (string) $measurement->manual_height_cm);
        $this->assertSame('7.30', (string) $measurement->manual_weight_kg);
        $this->assertSame('Koreksi hasil ukur', $measurement->notes);
        $this->assertSame('2026-05-20', $measurement->measured_at->toDateString());
        // Jam pencatatan asli dipertahankan walau tanggalnya diubah.
        $this->assertSame('09:30', $measurement->measured_at->format('H:i'));

        // Z-Score & kategori dihitung ulang dari tinggi + usia yang baru.
        $expectedZScore = Measurement::calculateZScore(
            64.5,
            (int) $anak->tanggal_lahir->diffInMonths($measurement->measured_at),
            $anak->jenis_kelamin
        );
        $this->assertSame(number_format($expectedZScore, 2, '.', ''), (string) $measurement->z_score);
        $this->assertSame(Measurement::getStuntingCategory($expectedZScore), $measurement->stunting_category);
    }

    public function test_measured_at_cannot_precede_birth_date_or_be_in_the_future(): void
    {
        [$petugas, $anak] = $this->createPosyanduContext('petugas-tanggal@example.com');

        $measurement = $this->createMeasurement($petugas, $anak, [
            'measured_at' => '2026-03-10 09:30:00',
        ]);

        $payload = [
            'height_cm' => 75,
            'weight_kg' => 9,
            'measured_at' => $anak->tanggal_lahir->copy()->subDay()->toDateString(),
        ];

        $this->actingAs($petugas)
            ->put(route('measurements.update', $measurement), $payload)
            ->assertSessionHasErrors('measured_at');

        $this->actingAs($petugas)
            ->put(route('measurements.update', $measurement), array_merge($payload, [
                'measured_at' => now()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('measured_at');

        $this->assertSame('2026-03-10', $measurement->fresh()->measured_at->toDateString());
    }

    public function test_other_petugas_in_same_posyandu_cannot_edit_or_update(): void
    {
        $posyandu = $this->createPosyandu('Posyandu Cempaka');
        $owner = $this->createPetugas($posyandu, 'petugas-pemilik@example.com');
        $other = $this->createPetugas($posyandu, 'petugas-lain@example.com');
        $anak = $this->createAnak($posyandu, $owner);

        $measurement = $this->createMeasurement($owner, $anak, [
            'height_cm' => 70.00,
            'measured_at' => '2026-03-10 09:30:00',
        ]);

        $this->actingAs($other)
            ->get(route('measurements.edit', $measurement))
            ->assertForbidden();

        $this->actingAs($other)
            ->put(route('measurements.update', $measurement), [
                'height_cm' => 100,
                'weight_kg' => 20,
                'measured_at' => '2026-05-20',
            ])
            ->assertForbidden();

        $this->assertSame('70.00', (string) $measurement->fresh()->height_cm);
    }

    public function test_history_table_shows_edit_action_only_for_the_recording_petugas(): void
    {
        $posyandu = $this->createPosyandu('Posyandu Dahlia');
        $owner = $this->createPetugas($posyandu, 'petugas-aksi-pemilik@example.com');
        $other = $this->createPetugas($posyandu, 'petugas-aksi-lain@example.com');
        $anak = $this->createAnak($posyandu, $owner);

        $measurement = $this->createMeasurement($owner, $anak, [
            'measured_at' => '2026-03-10 09:30:00',
        ]);

        $editUrl = route('measurements.edit', $measurement);

        $this->actingAs($owner)
            ->get(route('measurements.anak.show', $anak))
            ->assertOk()
            ->assertSee($editUrl, false);

        $this->actingAs($other)
            ->get(route('measurements.anak.show', $anak))
            ->assertOk()
            ->assertDontSee($editUrl, false);
    }

    /**
     * @return array{0: User, 1: Anak}
     */
    private function createPosyanduContext(string $email): array
    {
        $posyandu = $this->createPosyandu('Posyandu ' . $email);
        $petugas = $this->createPetugas($posyandu, $email);
        $anak = $this->createAnak($posyandu, $petugas);

        return [$petugas, $anak];
    }

    private function createPosyandu(string $name): Posyandu
    {
        return Posyandu::create([
            'nama' => $name,
            'kota' => 'Makassar',
            'provinsi' => 'Sulawesi Selatan',
            'status' => 'active',
        ]);
    }

    private function createPetugas(Posyandu $posyandu, string $email): User
    {
        $user = User::factory()->petugas()->create([
            'name' => 'Petugas ' . $email,
            'email' => $email,
        ]);

        PetugasProfile::create([
            'user_id' => $user->id,
            'posyandu_id' => $posyandu->id,
            'nama_lengkap' => 'Petugas ' . $email,
            'nik' => str_pad((string) random_int(1, 9999999999999999), 16, '0', STR_PAD_LEFT),
            'no_telepon' => '081234567890',
            'posyandu_name' => $posyandu->nama,
            'kota' => 'Makassar',
            'provinsi' => 'Sulawesi Selatan',
            'verified_at' => now(),
        ]);

        return $user->fresh('petugasProfile');
    }

    private function createAnak(Posyandu $posyandu, User $petugas, array $overrides = []): Anak
    {
        static $counter = 1;

        $anak = Anak::create(array_merge([
            'petugas_id' => $petugas->id,
            'posyandu_id' => $posyandu->id,
            'nama' => 'Anak Edit ' . $counter,
            'nik_anak' => str_pad((string) (3301123400000000 + $counter), 16, '0', STR_PAD_LEFT),
            'tanggal_lahir' => '2024-01-01',
            'tempat_lahir' => 'Makassar',
            'jenis_kelamin' => 'L',
            'no_kk' => '747100000000000' . $counter,
            'nama_ayah' => 'Ayah Edit ' . $counter,
            'nik_ayah' => str_pad((string) (7400000000000000 + $counter), 16, '0', STR_PAD_LEFT),
            'nama_ibu' => 'Ibu Edit ' . $counter,
            'nik_ibu' => str_pad((string) (7400000000001000 + $counter), 16, '0', STR_PAD_LEFT),
            'alamat' => 'Jl. Edit No. ' . $counter,
        ], $overrides));

        $counter++;

        return $anak->fresh('posyandu');
    }

    private function createMeasurement(User $petugas, Anak $anak, array $overrides = []): Measurement
    {
        $measurement = Measurement::create(array_merge([
            'user_id' => $petugas->id,
            'anak_id' => $anak->id,
            'child_name' => $anak->nama,
            'parent_name' => $anak->nama_ibu,
            'posyandu_name' => $anak->posyandu->nama,
            'address' => $anak->alamat,
            'birth_date' => $anak->tanggal_lahir,
            'gender' => $anak->jenis_kelamin,
            'height_cm' => 75,
            'weight_kg' => 9,
            'z_score' => -1.25,
            'stunting_category' => 'Normal',
            'measured_at' => '2026-03-10 09:30:00',
            'notes' => 'Catatan awal',
        ], $overrides));

        return $measurement->fresh();
    }
}
