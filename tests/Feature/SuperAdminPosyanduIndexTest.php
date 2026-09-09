<?php

namespace Tests\Feature;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminPosyanduIndexTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->superadmin()->create();
    }

    private function seedPosyandu(): void
    {
        Posyandu::create(['nama' => 'Posyandu Melati', 'kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Padangsambian', 'kota' => 'Denpasar', 'status' => 'active']);
        Posyandu::create(['nama' => 'Posyandu Mawar', 'kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Pemecutan', 'kota' => 'Denpasar', 'status' => 'active']);
        Posyandu::create(['nama' => 'Posyandu Anggrek', 'kecamatan' => 'Denpasar Timur', 'kelurahan' => 'Kesiman', 'kota' => 'Denpasar', 'status' => 'inactive']);
    }

    public function test_halaman_memakai_layout_admin_yang_sama_dengan_menu_lain(): void
    {
        $this->seedPosyandu();

        $response = $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index'))
            ->assertOk();

        // Kerangka admin (sidebar + kelas desain) yang dipakai menu Data Anak/Petugas.
        $response->assertSee('sidebar-nav', false);
        $response->assertSee('class="glass-card fade-in"', false);
        $response->assertSee('class="data-table"', false);
        $response->assertSee('Manajemen Posyandu');

        // Layout landing (halaman publik) tidak boleh ikut terbawa lagi.
        $response->assertDontSee('class="nav-links"', false);
    }

    public function test_filter_kecamatan(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['kecamatan' => 'Denpasar Timur']))
            ->assertOk()
            ->assertSee('Posyandu Anggrek')
            ->assertDontSee('Posyandu Melati')
            ->assertDontSee('Posyandu Mawar');
    }

    public function test_filter_desa(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['kelurahan' => 'Pemecutan']))
            ->assertOk()
            ->assertSee('Posyandu Mawar')
            ->assertDontSee('Posyandu Melati')
            ->assertDontSee('Posyandu Anggrek');
    }

    public function test_filter_kecamatan_dan_desa_digabung(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Padangsambian']))
            ->assertOk()
            ->assertSee('Posyandu Melati')
            ->assertDontSee('Posyandu Mawar');
    }

    public function test_desa_dari_kecamatan_lain_diabaikan_bukan_hasil_kosong(): void
    {
        $this->seedPosyandu();

        // Kesiman ada di Denpasar Timur, jadi pasangan ini mustahil cocok.
        // Filter desanya dibuang, hasilnya tetap seluruh posyandu di kecamatan itu.
        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['kecamatan' => 'Denpasar Barat', 'kelurahan' => 'Kesiman']))
            ->assertOk()
            ->assertSee('Posyandu Melati')
            ->assertSee('Posyandu Mawar')
            ->assertDontSee('Posyandu Anggrek');
    }

    public function test_dropdown_desa_hanya_berisi_desa_di_kecamatan_terpilih(): void
    {
        $this->seedPosyandu();

        $response = $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['kecamatan' => 'Denpasar Barat']))
            ->assertOk();

        $html = $response->getContent();
        $options = $this->optionValues($html, 'kelurahan');

        $this->assertEqualsCanonicalizing(['', 'Padangsambian', 'Pemecutan'], $options);
        $this->assertNotContains('Kesiman', $options);
    }

    public function test_dropdown_kecamatan_berisi_seluruh_kecamatan(): void
    {
        $this->seedPosyandu();

        $response = $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index'))
            ->assertOk();

        $this->assertEqualsCanonicalizing(
            ['', 'Denpasar Barat', 'Denpasar Timur'],
            $this->optionValues($response->getContent(), 'kecamatan')
        );
    }

    public function test_pencarian_juga_mencakup_desa(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['search' => 'Kesiman']))
            ->assertOk()
            ->assertSee('Posyandu Anggrek')
            ->assertDontSee('Posyandu Melati');
    }

    public function test_filter_status_tetap_berfungsi(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Posyandu Anggrek')
            ->assertDontSee('Posyandu Melati');
    }

    public function test_hitungan_chip_status_mengikuti_filter_wilayah(): void
    {
        $this->seedPosyandu();

        // Tanpa filter: 2 aktif (Melati, Mawar) + 1 nonaktif (Anggrek).
        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index'))
            ->assertOk()
            ->assertSee('Aktif: 2')
            ->assertSee('Nonaktif: 1');

        // Denpasar Barat hanya berisi 2 posyandu aktif, jadi chip nonaktif nol —
        // bukan ikut menghitung Anggrek yang ada di kecamatan lain.
        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['kecamatan' => 'Denpasar Barat']))
            ->assertOk()
            ->assertSee('Aktif: 2')
            ->assertSee('Nonaktif: 0');

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['kelurahan' => 'Kesiman']))
            ->assertOk()
            ->assertSee('Aktif: 0')
            ->assertSee('Nonaktif: 1');
    }

    public function test_empty_state_saat_filter_tidak_menghasilkan_apa_apa(): void
    {
        $this->seedPosyandu();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.index', ['search' => 'tidak-ada-posyandu-ini']))
            ->assertOk()
            ->assertSee('Tidak ada posyandu yang cocok')
            ->assertSee('Reset Filter');
    }

    public function test_halaman_tambah_dan_edit_memakai_layout_admin(): void
    {
        $this->seedPosyandu();
        $posyandu = Posyandu::first();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.create'))
            ->assertOk()
            ->assertSee('sidebar-nav', false)
            ->assertSee('Tambah Posyandu')
            ->assertSee('Desa / Kelurahan');

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.posyandu.edit', $posyandu))
            ->assertOk()
            ->assertSee('sidebar-nav', false)
            ->assertSee('Edit Posyandu')
            ->assertSee($posyandu->nama);
    }

    public function test_simpan_lewat_form_baru_masih_bekerja(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('super-admin.posyandu.store'), [
            'nama' => 'Posyandu Kenanga',
            'kecamatan' => 'Denpasar Utara',
            'kelurahan' => 'Peguyangan',
            'status' => 'active',
        ])->assertRedirect(route('super-admin.posyandu.index'));

        $posyandu = Posyandu::where('nama', 'Posyandu Kenanga')->firstOrFail();
        $this->assertSame('Peguyangan', $posyandu->kelurahan);

        $this->actingAs($admin)->put(route('super-admin.posyandu.update', $posyandu), [
            'nama' => 'Posyandu Kenanga',
            'kecamatan' => 'Denpasar Utara',
            'kelurahan' => 'Ubung',
            'status' => 'inactive',
        ])->assertRedirect(route('super-admin.posyandu.index'));

        $this->assertSame('Ubung', $posyandu->refresh()->kelurahan);
        $this->assertSame('inactive', $posyandu->status);
    }

    /** Ambil seluruh value <option> dari satu <select name="..."> */
    private function optionValues(string $html, string $selectName): array
    {
        $this->assertMatchesRegularExpression(
            '/<select name="'.$selectName.'"/',
            $html,
            "select[name={$selectName}] tidak ditemukan di halaman"
        );

        preg_match('/<select name="'.$selectName.'".*?>(.*?)<\/select>/s', $html, $m);
        preg_match_all('/<option value="([^"]*)"/', $m[1], $opts);

        return array_map(fn ($v) => html_entity_decode($v), $opts[1]);
    }
}
