<?php

namespace Tests\Feature;

use App\Models\Anak;
use App\Models\Measurement;
use App\Models\PetugasProfile;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasurementHistoryByChildTest extends TestCase
{
    use RefreshDatabase;

    public function test_measurement_index_groups_history_by_child_and_limits_to_same_posyandu(): void
    {
        $posyanduA = $this->createPosyandu('Posyandu Melati');
        $posyanduB = $this->createPosyandu('Posyandu Anggrek');

        $petugasA1 = $this->createPetugas($posyanduA, 'petugas-a1@example.com');
        $petugasA2 = $this->createPetugas($posyanduA, 'petugas-a2@example.com');
        $petugasB = $this->createPetugas($posyanduB, 'petugas-b@example.com');

        $anakA1 = $this->createAnak($posyanduA, $petugasA1, [
            'nama' => 'Budi Santoso',
            'nik_anak' => '3201123412341234',
        ]);
        $anakA2 = $this->createAnak($posyanduA, $petugasA1, [
            'nama' => 'Siti Aminah',
            'nik_anak' => '3201123412345678',
        ]);
        $anakB = $this->createAnak($posyanduB, $petugasB, [
            'nama' => 'Anak Luar Posyandu',
            'nik_anak' => '3201123499999999',
        ]);

        $this->createMeasurement($petugasA1, $anakA1, ['measured_at' => '2026-01-10 08:00:00']);
        $this->createMeasurement($petugasA2, $anakA1, ['measured_at' => '2026-04-01 09:00:00']);
        $this->createMeasurement($petugasA2, $anakA2, ['measured_at' => '2026-03-01 09:00:00']);
        $this->createMeasurement($petugasB, $anakB, ['measured_at' => '2026-04-02 09:00:00']);

        $response = $this->actingAs($petugasA1)->get(route('measurements.index'));

        $response->assertOk();
        $response->assertSeeInOrder([$anakA1->nama, $anakA2->nama]);
        $response->assertDontSeeText($anakB->nama);

        $content = $response->getContent();
        $this->assertSame(1, substr_count($content, $anakA1->nama));
        $this->assertSame(1, substr_count($content, $anakA2->nama));
    }

    public function test_measurement_history_shows_child_photo_from_measurement(): void
    {
        $posyandu = $this->createPosyandu('Posyandu Kenari');
        $petugas = $this->createPetugas($posyandu, 'petugas-foto@example.com');
        $anak = $this->createAnak($posyandu, $petugas, ['nama' => 'Nadia']);

        $measurement = $this->createMeasurement($petugas, $anak, [
            'photo_path' => 'measurements/nadia-latest.jpg',
            'measured_at' => '2026-04-02 08:00:00',
        ]);

        $this->actingAs($petugas)
            ->get(route('measurements.index'))
            ->assertOk()
            ->assertSee($measurement->photo_path, false);

        $this->actingAs($petugas)
            ->get(route('measurements.anak.show', $anak))
            ->assertOk()
            ->assertSee($measurement->photo_path, false);
    }

    public function test_child_history_page_shows_all_measurements_from_same_posyandu(): void
    {
        $posyandu = $this->createPosyandu('Posyandu Mawar');
        $petugas1 = $this->createPetugas($posyandu, 'petugas-1@example.com');
        $petugas2 = $this->createPetugas($posyandu, 'petugas-2@example.com');

        $anak = $this->createAnak($posyandu, $petugas1, ['nama' => 'Raisa Putri']);

        $measurement1 = $this->createMeasurement($petugas1, $anak, [
            'measured_at' => '2026-01-05 08:00:00',
            'height_cm' => 72.1,
            'weight_kg' => 8.4,
        ]);
        $measurement2 = $this->createMeasurement($petugas2, $anak, [
            'measured_at' => '2026-02-05 08:00:00',
            'height_cm' => 73.8,
            'weight_kg' => 8.8,
        ]);

        $response = $this->actingAs($petugas1)->get(route('measurements.anak.show', $anak));

        $response->assertOk();
        $response->assertSeeText('Grafik Perkembangan');
        $response->assertSee('id="growthChart"', false);
        $response->assertSeeText($measurement1->measured_at->format('d M Y'));
        $response->assertSeeText($measurement2->measured_at->format('d M Y'));
        $response->assertSeeText($petugas1->petugasProfile->nama_lengkap);
        $response->assertSeeText($petugas2->petugasProfile->nama_lengkap);
    }

    public function test_petugas_cannot_open_child_history_from_other_posyandu(): void
    {
        $posyanduA = $this->createPosyandu('Posyandu Kenanga');
        $posyanduB = $this->createPosyandu('Posyandu Flamboyan');

        $petugasA = $this->createPetugas($posyanduA, 'petugas-a@example.com');
        $petugasB = $this->createPetugas($posyanduB, 'petugas-b2@example.com');

        $anakB = $this->createAnak($posyanduB, $petugasB, ['nama' => 'Aisyah']);
        $this->createMeasurement($petugasB, $anakB);

        $this->actingAs($petugasA)
            ->get(route('measurements.anak.show', $anakB))
            ->assertForbidden();
    }

    public function test_petugas_can_view_same_posyandu_measurement_but_cannot_delete_other_petugas_record(): void
    {
        $posyandu = $this->createPosyandu('Posyandu Nusa');
        $petugas1 = $this->createPetugas($posyandu, 'petugas-viewer@example.com');
        $petugas2 = $this->createPetugas($posyandu, 'petugas-owner@example.com');

        $anak = $this->createAnak($posyandu, $petugas1, ['nama' => 'Ilham']);
        $measurement = $this->createMeasurement($petugas2, $anak, [
            'measured_at' => '2026-03-08 08:00:00',
        ]);

        $this->actingAs($petugas1)
            ->get(route('measurements.show', $measurement))
            ->assertOk()
            ->assertSeeText('Catatan dari petugas lain hanya bisa dilihat.');

        $this->actingAs($petugas1)
            ->delete(route('measurements.destroy', $measurement))
            ->assertForbidden();

        $this->assertDatabaseHas('measurements', ['id' => $measurement->id]);
    }

    public function test_store_uses_anak_as_snapshot_source_and_create_page_supports_prefill(): void
    {
        $posyandu = $this->createPosyandu('Posyandu Teratai');
        $petugas = $this->createPetugas($posyandu, 'petugas-store@example.com');

        $anak = $this->createAnak($posyandu, $petugas, [
            'nama' => 'Nabila',
            'nik_anak' => '3201123400001111',
            'nama_ibu' => 'Ibu Nabila',
            'alamat' => 'Jl. Melati No. 1',
            'jenis_kelamin' => 'P',
            'tanggal_lahir' => '2024-01-15',
        ]);

        $this->actingAs($petugas)
            ->get(route('measurements.create', ['anak_id' => $anak->id]))
            ->assertOk()
            ->assertSee($anak->nama, false)
            ->assertSee($anak->nik_anak, false)
            ->assertSee('name="anak_id" id="anakIdInput" value="'.$anak->id.'"', false);

        $response = $this->actingAs($petugas)->post(route('measurements.store'), [
            'anak_id' => $anak->id,
            'child_name' => 'Nama Salah',
            'parent_name' => 'Orang Tua Salah',
            'address' => 'Alamat Salah',
            'birth_date' => '2020-01-01',
            'gender' => 'L',
            'height_cm' => 82.5,
            'weight_kg' => 10.2,
            'measured_at' => '2026-04-01',
            'notes' => 'Kontrol rutin',
        ]);

        $response->assertRedirect(route('measurements.anak.show', $anak));

        $measurement = Measurement::query()->latest('id')->firstOrFail();

        $this->assertDatabaseHas('measurements', [
            'id' => $measurement->id,
            'anak_id' => $anak->id,
            'user_id' => $petugas->id,
            'child_name' => $anak->nama,
            'parent_name' => $anak->nama_ibu,
            'posyandu_name' => $posyandu->nama,
            'address' => $anak->alamat,
            'gender' => $anak->jenis_kelamin,
            'notes' => 'Kontrol rutin',
        ]);

        $this->assertSame($anak->tanggal_lahir->toDateString(), $measurement->birth_date?->toDateString());
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
        static $anakCounter = 1;

        return Anak::create(array_merge([
            'petugas_id' => $petugas->id,
            'posyandu_id' => $posyandu->id,
            'nama' => 'Anak ' . $anakCounter,
            'nik_anak' => str_pad((string) (3201123400000000 + $anakCounter), 16, '0', STR_PAD_LEFT),
            'tanggal_lahir' => '2024-01-01',
            'tempat_lahir' => 'Makassar',
            'jenis_kelamin' => 'L',
            'no_kk' => '737100000000000' . $anakCounter,
            'nama_ayah' => 'Ayah ' . $anakCounter,
            'nik_ayah' => str_pad((string) (7300000000000000 + $anakCounter), 16, '0', STR_PAD_LEFT),
            'nama_ibu' => 'Ibu ' . $anakCounter,
            'nik_ibu' => str_pad((string) (7300000000001000 + $anakCounter), 16, '0', STR_PAD_LEFT),
            'alamat' => 'Jl. Contoh No. ' . $anakCounter,
        ], $overrides));
    }

    private function createMeasurement(User $petugas, Anak $anak, array $overrides = []): Measurement
    {
        static $measurementCounter = 1;

        $measurement = Measurement::create(array_merge([
            'user_id' => $petugas->id,
            'anak_id' => $anak->id,
            'child_name' => $anak->nama,
            'parent_name' => $anak->nama_ibu,
            'posyandu_name' => $anak->posyandu->nama,
            'address' => $anak->alamat,
            'birth_date' => $anak->tanggal_lahir,
            'gender' => $anak->jenis_kelamin,
            'height_cm' => 70 + $measurementCounter,
            'weight_kg' => 8 + ($measurementCounter / 10),
            'z_score' => -1.25,
            'stunting_category' => 'Normal',
            'measured_at' => now()->addDays($measurementCounter),
            'notes' => 'Catatan ' . $measurementCounter,
        ], $overrides));

        $measurementCounter++;

        return $measurement->fresh();
    }
}
