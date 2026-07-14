<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Services\AntropometriService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AntropometriController extends Controller
{
    private const HARI_PER_BULAN = 30.4375;

    public function index(Request $request)
    {
        $user = Auth::user();
        [$anakOptions, $canPilihAnak] = $this->resolveAnakOptions($user);

        $selectedAnakId = $request->integer('anak_id') ?: null;
        $grafik = null;

        if ($canPilihAnak && $selectedAnakId && $anakOptions->contains('id', $selectedAnakId)) {
            $grafik = $this->buildGrafik($selectedAnakId);
        }

        return view('antropometri.index', [
            'indikator' => AntropometriService::INDIKATOR,
            'kategori' => AntropometriService::KATEGORI,
            'kenaikanBb' => AntropometriService::KENAIKAN_BB_MINIMUM,
            'kenaikanBbDiatas12' => AntropometriService::KENAIKAN_BB_MINIMUM_DIATAS_12_BULAN,
            'anakOptions' => $anakOptions,
            'canPilihAnak' => $canPilihAnak,
            'selectedAnakId' => $selectedAnakId,
            'grafik' => $grafik,
            'tabelBoys' => AntropometriService::tabelLengkap('L'),
            'tabelGirls' => AntropometriService::tabelLengkap('P'),
        ]);
    }

    /**
     * @return array{0: Collection, 1: bool}
     */
    private function resolveAnakOptions($user): array
    {
        if ($user->isPetugasPosyandu()) {
            $posyanduId = $user->petugasProfile?->posyandu_id;

            if (! $posyanduId) {
                return [collect(), true];
            }

            $anak = Anak::where('posyandu_id', $posyanduId)
                ->orderBy('nama')
                ->get(['id', 'nama', 'jenis_kelamin', 'tanggal_lahir']);

            return [$anak, true];
        }

        if ($user->isOrangTua()) {
            $anak = Anak::forOrangTua($user->id)
                ->orderBy('nama')
                ->get(['id', 'nama', 'jenis_kelamin', 'tanggal_lahir']);

            return [$anak, true];
        }

        // Super admin: hanya melihat tabel acuan, tanpa pilih anak spesifik.
        return [collect(), false];
    }

    private function buildGrafik(int $anakId): array
    {
        $anak = Anak::with(['measurements' => fn ($q) => $q->orderBy('measured_at')])->findOrFail($anakId);
        $gender = $anak->jenis_kelamin;
        $lahir = $anak->tanggal_lahir;

        $titik = $anak->measurements->map(function ($m) use ($lahir) {
            $umurBulan = $lahir->diffInDays($m->measured_at) / self::HARI_PER_BULAN;

            return [
                'measured_at' => $m->measured_at->format('Y-m-d'),
                'umur_bulan' => round($umurBulan, 1),
                'weight_kg' => (float) $m->weight_kg,
                'height_cm' => (float) $m->height_cm,
            ];
        })->values();

        $standarBb = $this->kurvaStandarUmur('bb_u', $gender);
        $standarTb = $this->kurvaStandarUmur('pb_tb_u', $gender);

        $latest = $anak->measurements->last();
        $statusTerkini = null;
        $penilaianKenaikan = null;

        if ($latest) {
            $umurLatest = $lahir->diffInDays($latest->measured_at) / self::HARI_PER_BULAN;
            $beratKg = (float) $latest->weight_kg;
            $tinggiCm = (float) $latest->height_cm;
            $imt = $tinggiCm > 0 ? $beratKg / (($tinggiCm / 100) ** 2) : null;

            $zBbU = AntropometriService::zScoreUmur('bb_u', $gender, $beratKg, $umurLatest);
            $zPbTbU = AntropometriService::zScoreUmur('pb_tb_u', $gender, $tinggiCm, $umurLatest);
            $zBbPbTb = AntropometriService::zScoreBeratMenurutPanjangTinggi($gender, $beratKg, $tinggiCm, $umurLatest);
            $zImtU = $imt ? AntropometriService::zScoreUmur('imt_u', $gender, $imt, $umurLatest) : null;

            $statusTerkini = [
                'umur_bulan' => round($umurLatest, 1),
                'berat_kg' => $beratKg,
                'tinggi_cm' => $tinggiCm,
                'imt' => $imt ? round($imt, 2) : null,
                'bb_u' => ['z' => $zBbU, ...AntropometriService::klasifikasi('bb_u', $zBbU)],
                'pb_tb_u' => ['z' => $zPbTbU, ...AntropometriService::klasifikasi('pb_tb_u', $zPbTbU)],
                'bb_pb_tb' => ['z' => $zBbPbTb, ...AntropometriService::klasifikasi('bb_pb_tb', $zBbPbTb)],
                'imt_u' => ['z' => $zImtU, ...AntropometriService::klasifikasi('imt_u', $zImtU)],
            ];

            if ($anak->measurements->count() >= 2) {
                $prev = $anak->measurements->slice(-2, 1)->first();
                $umurPrev = $lahir->diffInDays($prev->measured_at) / self::HARI_PER_BULAN;
                $kenaikanGram = ((float) $latest->weight_kg - (float) $prev->weight_kg) * 1000;

                $penilaian = AntropometriService::nilaiKenaikanBerat((int) floor($umurPrev), $kenaikanGram);
                $penilaianKenaikan = [
                    'dari_tanggal' => $prev->measured_at->format('d M Y'),
                    'ke_tanggal' => $latest->measured_at->format('d M Y'),
                    'kenaikan_gram' => round($kenaikanGram),
                    'status' => $penilaian['status'],
                    'minimum_gram' => $penilaian['minimum'],
                ];
            }
        }

        return [
            'anak' => $anak,
            'titik' => $titik,
            'standar_bb' => $standarBb,
            'standar_tb' => $standarTb,
            'status_terkini' => $statusTerkini,
            'penilaian_kenaikan' => $penilaianKenaikan,
        ];
    }

    private function kurvaStandarUmur(string $indikator, string $gender): array
    {
        $rows = AntropometriService::tabelUmurBulanan($indikator, $gender);

        $out = [];
        foreach ($rows as $bulan => $row) {
            $out[] = [
                'bulan' => $bulan,
                'median' => $row['SD0'],
                'sd2neg' => $row['SD2neg'],
                'sd3neg' => $row['SD3neg'],
                'sd2' => $row['SD2'],
                'sd3' => $row['SD3'],
            ];
        }

        return $out;
    }
}
