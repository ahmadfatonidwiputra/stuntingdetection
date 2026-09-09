<?php

namespace Tests\Feature;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminLaporanFilterTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->superadmin()->create();
    }

    private function seedPosyandu(): void
    {
        Posyandu::create(['nama' => 'Posyandu Melati', 'kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Padangsambian', 'kota' => 'Denpasar', 'provinsi' => 'Bali', 'status' => 'active']);
        Posyandu::create(['nama' => 'Posyandu Mawar', 'kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Pemecutan', 'kota' => 'Denpasar', 'provinsi' => 'Bali', 'status' => 'active']);
        Posyandu::create(['nama' => 'Posyandu Anggrek', 'kecamatan' => 'Denpasar Timur', 'kelurahan' => 'Kesiman', 'kota' => 'Denpasar', 'provinsi' => 'Bali', 'status' => 'active']);
    }

    public function test_filter_kecamatan(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Timur']))
            ->assertOk()
            ->assertSee('Posyandu Anggrek')
            ->assertDontSee('Posyandu Melati')
            ->assertDontSee('Posyandu Mawar');
    }

    public function test_filter_desa(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kelurahan' => 'Pemecutan']))
            ->assertOk()
            ->assertSee('Posyandu Mawar')
            ->assertDontSee('Posyandu Melati')
            ->assertDontSee('Posyandu Anggrek');
    }

    public function test_filter_kecamatan_dan_desa_digabung(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Padangsambian']))
            ->assertOk()
            ->assertSee('Posyandu Melati')
            ->assertDontSee('Posyandu Mawar');
    }

    public function test_filter_wilayah_bisa_digabung_dengan_pencarian(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Barat', 'search' => 'Mawar']))
            ->assertOk()
            ->assertSee('Posyandu Mawar')
            ->assertDontSee('Posyandu Melati');

        // Pencarian yang tidak berada di kecamatan terpilih tidak boleh lolos.
        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Barat', 'search' => 'Anggrek']))
            ->assertOk()
            ->assertDontSee('Posyandu Anggrek')
            ->assertSee('Tidak ditemukan posyandu');
    }

    public function test_desa_dari_kecamatan_lain_diabaikan_bukan_hasil_kosong(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Kesiman']))
            ->assertOk()
            ->assertSee('Posyandu Melati')
            ->assertSee('Posyandu Mawar')
            ->assertDontSee('Posyandu Anggrek');
    }

    public function test_dropdown_desa_hanya_berisi_desa_di_kecamatan_terpilih(): void
    {
        $this->seedPosyandu();

        $html = $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Barat']))
            ->assertOk()
            ->getContent();

        $this->assertEqualsCanonicalizing(['', 'Padangsambian', 'Pemecutan'], $this->optionValues($html, 'kelurahan'));
        $this->assertEqualsCanonicalizing(['', 'Denpasar Barat', 'Denpasar Timur'], $this->optionValues($html, 'kecamatan'));
    }

    public function test_kolom_wilayah_menampilkan_kecamatan_dan_desa(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index'))
            ->assertOk()
            ->assertSee('Denpasar Barat')
            ->assertSee('Padangsambian');
    }

    public function test_filter_ikut_terbawa_saat_pindah_halaman(): void
    {
        foreach (range(1, 20) as $i) {
            Posyandu::create(['nama' => "Posyandu Barat {$i}", 'kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Pemecutan', 'status' => 'active']);
        }
        Posyandu::create(['nama' => 'Posyandu Timur', 'kecamatan' => 'Denpasar Timur', 'kelurahan' => 'Kesiman', 'status' => 'active']);

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Barat', 'page' => 2]))
            ->assertOk()
            ->assertDontSee('Posyandu Timur');
    }

    public function test_reset_muncul_hanya_saat_ada_filter(): void
    {
        $this->seedPosyandu();
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('super-admin.laporan.index'))->assertOk()->assertDontSee('>Reset<', false);
        $this->actingAs($admin)->get(route('super-admin.laporan.index', ['kecamatan' => 'Denpasar Barat']))->assertOk()->assertSee('>Reset<', false);
    }

    /** Ambil seluruh value <option> dari satu <select name="..."> */
    private function optionValues(string $html, string $selectName): array
    {
        preg_match('/<select name="'.$selectName.'".*?>(.*?)<\/select>/s', $html, $m);
        $this->assertNotEmpty($m, "select[name={$selectName}] tidak ditemukan");
        preg_match_all('/<option value="([^"]*)"/', $m[1], $opts);

        return array_map(fn ($v) => html_entity_decode($v), $opts[1]);
    }
}
