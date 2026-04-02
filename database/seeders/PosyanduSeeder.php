<?php

namespace Database\Seeders;

use App\Models\Posyandu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PosyanduSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama'          => 'Posyandu Melati 1',
                'kode_posyandu' => 'POS-MLT-001',
                'alamat'        => 'Jl. Melati No. 10, RT 01/RW 02',
                'kelurahan'     => 'Denpasar Barat',
                'kecamatan'     => 'Denpasar',
                'kota'          => 'Denpasar',
                'provinsi'      => 'Bali',
                'no_telepon'    => '0361123456',
                'jadwal_buka'   => json_encode(['senin' => '08:00-12:00', 'rabu' => '08:00-12:00', 'jumat' => '08:00-12:00']),
                'status'        => 'active',
            ],
            [
                'nama'          => 'Posyandu Anggrek',
                'kode_posyandu' => 'POS-AGR-002',
                'alamat'        => 'Jl. Anggrek No. 5, RT 03/RW 01',
                'kelurahan'     => 'Denpasar Timur',
                'kecamatan'     => 'Denpasar',
                'kota'          => 'Denpasar',
                'provinsi'      => 'Bali',
                'no_telepon'    => '0361234567',
                'jadwal_buka'   => json_encode(['selasa' => '08:00-12:00', 'kamis' => '08:00-12:00']),
                'status'        => 'active',
            ],
            [
                'nama'          => 'Posyandu Mawar',
                'kode_posyandu' => 'POS-MAW-003',
                'alamat'        => 'Jl. Kenanga No. 5',
                'kelurahan'     => 'Cipete',
                'kecamatan'     => 'Cilandak',
                'kota'          => 'Jakarta Selatan',
                'provinsi'      => 'DKI Jakarta',
                'no_telepon'    => '02112345678',
                'jadwal_buka'   => json_encode(['senin' => '08:00-12:00', 'kamis' => '08:00-12:00']),
                'status'        => 'active',
            ],
            [
                'nama'          => 'Posyandu Dahlia',
                'kode_posyandu' => 'POS-DAH-004',
                'alamat'        => 'Jl. Anggrek No. 3',
                'kelurahan'     => 'Lebak Bulus',
                'kecamatan'     => 'Cilandak',
                'kota'          => 'Jakarta Selatan',
                'provinsi'      => 'DKI Jakarta',
                'no_telepon'    => '02187654321',
                'jadwal_buka'   => json_encode(['rabu' => '08:00-12:00', 'sabtu' => '08:00-11:00']),
                'status'        => 'active',
            ],
        ];

        foreach ($data as $item) {
            Posyandu::firstOrCreate(['nama' => $item['nama']], $item);
        }
    }
}
