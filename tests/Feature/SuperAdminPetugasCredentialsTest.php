<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminPetugasCredentialsTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->superadmin()->create();
    }

    private function petugas(): User
    {
        return User::factory()->create([
            'role' => 'petugas_posyandu',
            'status' => 'active',
            'password' => Hash::make('lama-sekali'),
        ]);
    }

    public function test_halaman_detail_menampilkan_kartu_kredensial(): void
    {
        $petugas = $this->petugas();

        $this->actingAs($this->superadmin())
            ->get(route('super-admin.petugas.show', $petugas))
            ->assertOk()
            ->assertSee('Kredensial Akun')
            ->assertSee($petugas->email)
            ->assertSee('Reset Password')
            ->assertSee('Ubah Username');
    }

    public function test_reset_password_otomatis_menghasilkan_password_baru(): void
    {
        $petugas = $this->petugas();
        $oldHash = $petugas->password;
        $oldToken = $petugas->remember_token;

        $response = $this->actingAs($this->superadmin())
            ->from(route('super-admin.petugas.show', $petugas))
            ->post(route('super-admin.petugas.reset-password', $petugas), ['mode' => 'auto']);

        $response->assertRedirect(route('super-admin.petugas.show', $petugas));
        $response->assertSessionHas('new_password');

        $newPassword = session('new_password');
        $this->assertSame(10, strlen($newPassword));
        $this->assertMatchesRegularExpression('/^[A-Za-z2-9]+$/', $newPassword);
        $this->assertDoesNotMatchRegularExpression('/[01OIl]/', $newPassword);

        $petugas->refresh();
        $this->assertNotSame($oldHash, $petugas->password);
        $this->assertNotSame($oldToken, $petugas->remember_token);
        $this->assertTrue(Hash::check($newPassword, $petugas->password));
    }

    public function test_password_baru_ditampilkan_sekali_di_halaman_detail(): void
    {
        $petugas = $this->petugas();
        $this->actingAs($this->superadmin());

        $this->post(route('super-admin.petugas.reset-password', $petugas), [
            'mode' => 'manual',
            'password' => 'Abc23xyz9K',
            'password_confirmation' => 'Abc23xyz9K',
        ])->assertSessionHasNoErrors();

        // Halaman tepat setelah reset menampilkan password barunya.
        $this->get(route('super-admin.petugas.show', $petugas))
            ->assertOk()
            ->assertSee('Password Baru')
            ->assertSee('Abc23xyz9K');

        // Flash sudah habis: muat ulang halaman tidak lagi memuat password.
        $this->get(route('super-admin.petugas.show', $petugas))
            ->assertOk()
            ->assertDontSee('Abc23xyz9K');
    }

    public function test_reset_password_manual(): void
    {
        $petugas = $this->petugas();

        $this->actingAs($this->superadmin())
            ->post(route('super-admin.petugas.reset-password', $petugas), [
                'mode' => 'manual',
                'password' => 'RahasiaBaru123',
                'password_confirmation' => 'RahasiaBaru123',
            ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('RahasiaBaru123', $petugas->refresh()->password));
    }

    public function test_reset_password_manual_menolak_password_pendek_dan_tidak_cocok(): void
    {
        $petugas = $this->petugas();
        $admin = $this->superadmin();

        $this->actingAs($admin)
            ->post(route('super-admin.petugas.reset-password', $petugas), [
                'mode' => 'manual', 'password' => 'abc', 'password_confirmation' => 'abc',
            ])->assertSessionHasErrors('password');

        $this->actingAs($admin)
            ->post(route('super-admin.petugas.reset-password', $petugas), [
                'mode' => 'manual', 'password' => 'RahasiaBaru123', 'password_confirmation' => 'beda123456',
            ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('lama-sekali', $petugas->refresh()->password));
    }

    public function test_ubah_username(): void
    {
        $petugas = $this->petugas();

        $this->actingAs($this->superadmin())
            ->post(route('super-admin.petugas.update-username', $petugas), ['email' => 'baru@posyandu.test'])
            ->assertSessionHasNoErrors();

        $petugas->refresh();
        $this->assertSame('baru@posyandu.test', $petugas->email);
        $this->assertNotNull($petugas->email_verified_at, 'status verifikasi tidak boleh hilang');
    }

    public function test_username_harus_unik(): void
    {
        $petugas = $this->petugas();
        $lain = User::factory()->create(['email' => 'dipakai@posyandu.test']);

        $this->actingAs($this->superadmin())
            ->post(route('super-admin.petugas.update-username', $petugas), ['email' => 'dipakai@posyandu.test'])
            ->assertSessionHasErrors('email');
    }

    public function test_target_bukan_petugas_ditolak(): void
    {
        $orangTua = User::factory()->create(['role' => 'orang_tua']);

        $this->actingAs($this->superadmin())
            ->post(route('super-admin.petugas.reset-password', $orangTua), ['mode' => 'auto'])
            ->assertNotFound();
    }

    public function test_petugas_tidak_bisa_mereset_password_petugas_lain(): void
    {
        $petugas = $this->petugas();

        $this->actingAs($this->petugas())
            ->post(route('super-admin.petugas.reset-password', $petugas), ['mode' => 'auto'])
            ->assertForbidden();

        $this->assertTrue(Hash::check('lama-sekali', $petugas->refresh()->password));
    }
}
