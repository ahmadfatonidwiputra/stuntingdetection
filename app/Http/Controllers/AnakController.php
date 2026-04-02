<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\OrangTuaAnak;
use App\Models\OrangTuaProfile;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnakController extends Controller
{
    /**
     * List anak (filtered by posyandu petugas jika bukan super_admin).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Anak::with(['posyandu', 'measurements' => fn($q) => $q->latest('measured_at')->limit(1)]);

        if ($user->isPetugasPosyandu()) {
            $posyanduId = $user->petugasProfile?->posyandu_id;
            if ($posyanduId) {
                $query->where('posyandu_id', $posyanduId);
            }
        }

        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nik_anak', 'LIKE', "%{$search}%")
                  ->orWhere('nama_ayah', 'LIKE', "%{$search}%")
                  ->orWhere('nama_ibu', 'LIKE', "%{$search}%");
            });
        }

        $anak = $query->latest()->paginate(20)->withQueryString();
        return view('anak.index', compact('anak', 'search'));
    }

    /**
     * Form tambah anak.
     */
    public function create()
    {
        $user     = auth()->user();
        $defaultPosyanduId = $user->petugasProfile?->posyandu_id;
        
        if ($user->isPetugasPosyandu() && $defaultPosyanduId) {
            $posyandu = Posyandu::where('id', $defaultPosyanduId)->get();
        } else {
            $posyandu = Posyandu::active()->orderBy('nama')->get();
        }
        
        return view('anak.create', compact('posyandu', 'defaultPosyanduId'));
    }

    /**
     * Simpan anak baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik_anak'       => 'required|digits:16|unique:anak,nik_anak',
            'no_kk'          => 'required|digits:16',
            'nama'           => 'required|string|max:200',
            'nama_ayah'      => 'required|string|max:200',
            'nama_ibu'       => 'required|string|max:200',
            'tanggal_lahir'  => 'required|date|before:today',
            'tempat_lahir'   => 'required|string|max:100',
            'alamat'         => 'required|string',
            'jenis_kelamin'  => 'required|in:L,P',
            'posyandu_id'    => 'required|exists:posyandu,id',
            'nik_ayah'       => 'nullable|digits:16',
            'nik_ibu'        => 'nullable|digits:16',
            'no_telepon_ortu'=> 'nullable|max:15',
            'email_ortu'     => 'nullable|email',
        ]);

        $validated['petugas_id'] = auth()->id();
        
        if (auth()->user()->isPetugasPosyandu()) {
            $petugasPosyanduId = auth()->user()->petugasProfile?->posyandu_id;
            if ($petugasPosyanduId) {
                $validated['posyandu_id'] = $petugasPosyanduId;
            }
        }

        $anak = Anak::create($validated);

        return redirect()->route('anak.index')
            ->with('success', "Data anak {$anak->nama} berhasil ditambahkan.");
    }

    /**
     * Detail anak.
     */
    public function show(Anak $anak)
    {
        $anak->load(['posyandu', 'petugas', 'measurements' => fn($q) => $q->orderBy('measured_at')]);
        return view('anak.show', compact('anak'));
    }

    /**
     * Form edit anak.
     */
    public function edit(Anak $anak)
    {
        $user = auth()->user();
        $defaultPosyanduId = $user->petugasProfile?->posyandu_id;
        
        if ($user->isPetugasPosyandu() && $defaultPosyanduId) {
            $posyandu = Posyandu::where('id', $defaultPosyanduId)->get();
        } else {
            $posyandu = Posyandu::active()->orderBy('nama')->get();
        }

        return view('anak.edit', compact('anak', 'posyandu'));
    }

    /**
     * Update data anak.
     */
    public function update(Request $request, Anak $anak)
    {
        $validated = $request->validate([
            'nik_anak'       => 'required|digits:16|unique:anak,nik_anak,' . $anak->id,
            'no_kk'          => 'required|digits:16',
            'nama'           => 'required|string|max:200',
            'nama_ayah'      => 'required|string|max:200',
            'nama_ibu'       => 'required|string|max:200',
            'tanggal_lahir'  => 'required|date|before:today',
            'tempat_lahir'   => 'required|string|max:100',
            'alamat'         => 'required|string',
            'jenis_kelamin'  => 'required|in:L,P',
            'posyandu_id'    => 'required|exists:posyandu,id',
            'nik_ayah'       => 'nullable|digits:16',
            'nik_ibu'        => 'nullable|digits:16',
            'no_telepon_ortu'=> 'nullable|max:15',
            'email_ortu'     => 'nullable|email',
        ]);

        if (auth()->user()->isPetugasPosyandu()) {
            $petugasPosyanduId = auth()->user()->petugasProfile?->posyandu_id;
            if ($petugasPosyanduId) {
                $validated['posyandu_id'] = $petugasPosyanduId;
            }
        }

        $anak->update($validated);

        return redirect()->route('anak.show', $anak)
            ->with('success', "Data {$anak->nama} berhasil diperbarui.");
    }

    /**
     * Hapus anak.
     */
    public function destroy(Anak $anak)
    {
        $nama = $anak->nama;
        $anak->delete();
        return redirect()->route('anak.index')->with('success', "Data anak {$nama} berhasil dihapus.");
    }

    /**
     * Autocomplete search orang tua (for AJAX).
     */
    public function searchOrangTua(Request $request)
    {
        $q = $request->input('q', '');
        $user = auth()->user();

        $query = Anak::select('nama_ayah', 'nik_ayah', 'nama_ibu', 'nik_ibu', 'no_kk', 'alamat', 'no_telepon_ortu', 'email_ortu');

        if ($user->isPetugasPosyandu()) {
            $posyanduId = $user->petugasProfile?->posyandu_id;
            if ($posyanduId) {
                $query->where('posyandu_id', $posyanduId);
            }
        }

        $results = $query->where(function ($qu) use ($q) {
                $qu->where('nama_ayah', 'LIKE', "%{$q}%")
                   ->orWhere('nama_ibu', 'LIKE', "%{$q}%");
            })
            ->distinct()
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'nama_ayah'  => $item->nama_ayah,
                'nik_ayah'   => $item->nik_ayah,
                'nama_ibu'   => $item->nama_ibu,
                'nik_ibu'    => $item->nik_ibu,
                'no_kk'      => $item->no_kk,
                'alamat'     => $item->alamat,
                'no_telepon' => $item->no_telepon_ortu,
                'email'      => $item->email_ortu,
            ]);

        return response()->json($results);
    }
}
