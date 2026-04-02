# Plan Update: Role Orang Tua & UX Improvement Petugas
# AI Stunt Detect System - Version 2.0

## 📋 RINGKASAN UPDATE

### New Requirements:
1. **Role Orang Tua** - Bisa melihat tumbuh kembang anak sendiri
2. **Privacy & Security** - Orang tua hanya bisa lihat data anaknya sendiri
3. **Registrasi Orang Tua** - Dengan validasi KK dan NIK Anak
4. **Dropdown Posyandu** - Petugas pilih dari list yang sudah terdaftar
5. **Auto-fill Data Anak** - Berdasarkan nama orang tua yang dipilih

---

## 1. UPDATE DATABASE SCHEMA

### 1.1 Update Tabel Users
```sql
-- Update ENUM role untuk menambahkan 'orang_tua'
ALTER TABLE users 
DROP CONSTRAINT IF EXISTS users_role_check;

ALTER TABLE users 
ADD CONSTRAINT users_role_check 
CHECK (role IN ('super_admin', 'petugas_posyandu', 'orang_tua'));

-- Set default untuk orang tua
ALTER TABLE users 
ALTER COLUMN role SET DEFAULT 'orang_tua';
```

### 1.2 Tabel Posyandu (Master Data - NEW)
```sql
CREATE TABLE posyandu (
  id SERIAL PRIMARY KEY,
  nama VARCHAR(200) NOT NULL UNIQUE,
  kode_posyandu VARCHAR(20) UNIQUE,
  alamat TEXT,
  kelurahan VARCHAR(100),
  kecamatan VARCHAR(100),
  kota VARCHAR(100),
  provinsi VARCHAR(100),
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  no_telepon VARCHAR(15),
  jadwal_buka TEXT, -- JSON: {"senin": "08:00-12:00", ...}
  status VARCHAR(20) DEFAULT 'active', -- active, inactive
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Index untuk pencarian cepat
CREATE INDEX idx_posyandu_nama ON posyandu(nama);
CREATE INDEX idx_posyandu_kota ON posyandu(kota);
CREATE INDEX idx_posyandu_status ON posyandu(status);
```

### 1.3 Update Tabel Petugas Profile
```sql
-- Ubah posyandu_name jadi foreign key ke tabel posyandu
ALTER TABLE petugas_profile
DROP COLUMN posyandu_name,
DROP COLUMN posyandu_address,
DROP COLUMN kelurahan,
DROP COLUMN kecamatan,
DROP COLUMN kota,
DROP COLUMN provinsi;

ALTER TABLE petugas_profile
ADD COLUMN posyandu_id INTEGER REFERENCES posyandu(id) ON DELETE SET NULL;

-- Index
CREATE INDEX idx_petugas_posyandu ON petugas_profile(posyandu_id);
```

### 1.4 Update Tabel Anak
```sql
-- Tambahkan kolom untuk data keluarga dan orang tua
ALTER TABLE anak
ADD COLUMN no_kk VARCHAR(16), -- Nomor Kartu Keluarga
ADD COLUMN nik_anak VARCHAR(16) UNIQUE, -- NIK Anak (sudah ada tapi pastikan unique)
ADD COLUMN nama_ayah VARCHAR(200),
ADD COLUMN nik_ayah VARCHAR(16),
ADD COLUMN nama_ibu VARCHAR(200),
ADD COLUMN nik_ibu VARCHAR(16),
ADD COLUMN no_telepon_ortu VARCHAR(15),
ADD COLUMN email_ortu VARCHAR(100),
ADD COLUMN posyandu_id INTEGER REFERENCES posyandu(id) ON DELETE SET NULL;

-- Update kolom yang sudah ada
ALTER TABLE anak
ALTER COLUMN nama_ortu DROP NOT NULL, -- Akan diganti dengan nama_ayah/nama_ibu
ALTER COLUMN nik_anak SET NOT NULL; -- NIK anak wajib

-- Index untuk pencarian cepat
CREATE INDEX idx_anak_kk ON anak(no_kk);
CREATE INDEX idx_anak_nik ON anak(nik_anak);
CREATE INDEX idx_anak_posyandu ON anak(posyandu_id);
CREATE INDEX idx_anak_petugas ON anak(petugas_id);
```

### 1.5 Tabel Orang Tua User (NEW)
```sql
CREATE TABLE orang_tua_profile (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  nama_lengkap VARCHAR(200) NOT NULL,
  nik VARCHAR(16) UNIQUE NOT NULL,
  no_kk VARCHAR(16) NOT NULL, -- Nomor Kartu Keluarga
  hubungan VARCHAR(10) CHECK (hubungan IN ('ayah', 'ibu', 'wali')),
  no_telepon VARCHAR(15),
  alamat TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE(user_id) -- Satu user hanya punya satu profile
);

-- Index
CREATE INDEX idx_ortu_nik ON orang_tua_profile(nik);
CREATE INDEX idx_ortu_kk ON orang_tua_profile(no_kk);
CREATE INDEX idx_ortu_user ON orang_tua_profile(user_id);
```

### 1.6 Tabel Relasi Orang Tua - Anak (NEW)
```sql
-- Junction table untuk relasi many-to-many
-- Karena 1 orang tua bisa punya banyak anak
-- Dan 1 anak bisa punya 2 orang tua (ayah & ibu)
CREATE TABLE orang_tua_anak (
  id SERIAL PRIMARY KEY,
  orang_tua_id INTEGER NOT NULL REFERENCES orang_tua_profile(id) ON DELETE CASCADE,
  anak_id INTEGER NOT NULL REFERENCES anak(id) ON DELETE CASCADE,
  hubungan VARCHAR(10) CHECK (hubungan IN ('ayah', 'ibu', 'wali')),
  is_primary BOOLEAN DEFAULT false, -- Primary guardian (untuk notifikasi)
  verified_at TIMESTAMP, -- Kapan relasi ini diverifikasi
  verified_by INTEGER REFERENCES users(id), -- Petugas yang verifikasi
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  UNIQUE(orang_tua_id, anak_id) -- Satu orang tua tidak bisa diduplikasi untuk anak yang sama
);

-- Index
CREATE INDEX idx_ortu_anak_ortu ON orang_tua_anak(orang_tua_id);
CREATE INDEX idx_ortu_anak_anak ON orang_tua_anak(anak_id);
CREATE INDEX idx_ortu_anak_primary ON orang_tua_anak(is_primary);
```

---

## 2. ALUR KERJA SISTEM (UPDATED)

### 2.1 Alur Registrasi Orang Tua

```
1. Orang tua buka landing page
2. Klik "Daftar Sebagai Orang Tua"
3. Isi form registrasi:
   ┌─────────────────────────────────────┐
   │ • Username & Email & Password       │
   │ • Nama Lengkap                      │
   │ • NIK (Orang Tua)                   │
   │ • No. Kartu Keluarga (KK)           │
   │ • Hubungan (Ayah/Ibu/Wali)          │
   │ • No. Telepon                       │
   │ • NIK Anak (untuk validasi)         │
   │ • Nama Anak (untuk validasi)        │
   └─────────────────────────────────────┘
4. Submit → Backend validasi:
   ✓ Cek NIK Anak ada di database?
   ✓ Cek No. KK cocok dengan data anak?
   ✓ Cek NIK Orang Tua belum terdaftar?
   
5. Jika validasi GAGAL:
   → Tampilkan error: 
      "Data tidak cocok. Pastikan NIK Anak dan No. KK sudah 
       tercatat di posyandu. Hubungi petugas posyandu Anda."
   
6. Jika validasi BERHASIL:
   → Status: PENDING (menunggu verifikasi petugas)
   → Email: "Registrasi berhasil, menunggu verifikasi petugas"
   → Notifikasi ke petugas posyandu terkait
   
7. Petugas verifikasi:
   → Petugas cek data orang tua & anak
   → Approve → Status: ACTIVE, link orang_tua_anak dibuat
   → Reject → Status: REJECTED, kirim alasan
   
8. Orang tua bisa login (jika ACTIVE)
```

### 2.2 Alur Login Orang Tua

```
1. Orang tua buka /login
2. Input username/email & password
3. Sistem cek role = 'orang_tua'
4. Cek status:
   - ACTIVE → redirect ke /orang-tua/dashboard
   - PENDING → tampilkan "Menunggu Verifikasi Petugas"
   - REJECTED → tampilkan "Pendaftaran Ditolak: [alasan]"
5. Dashboard orang tua:
   - List anak yang terhubung dengan akun orang tua
   - Grafik tumbuh kembang per anak
   - Riwayat pemeriksaan
```

### 2.3 Alur Input Data Anak oleh Petugas (UPDATED)

**SEBELUM (Lama):**
```
1. Petugas pilih menu "Tambah Anak"
2. Isi manual semua field:
   - Nama anak
   - NIK anak
   - Tanggal lahir
   - Jenis kelamin
   - Nama orang tua
   - Alamat
   - Nama posyandu (ketik manual)
3. Submit
```

**SESUDAH (Baru - UX Improvement):**
```
1. Petugas pilih menu "Tambah Anak"
2. Step 1 - Pilih Posyandu:
   ┌─────────────────────────────────────┐
   │ Posyandu: [Dropdown Select]         │
   │  ▼ Pilih Posyandu                   │
   │    • Posyandu Melati 1              │
   │    • Posyandu Melati 2              │
   │    • Posyandu Anggrek               │
   └─────────────────────────────────────┘
   
3. Step 2 - Cari Orang Tua (Search dengan Autocomplete):
   ┌─────────────────────────────────────┐
   │ Cari Nama Orang Tua: [________]     │
   │                                      │
   │ Hasil Pencarian:                    │
   │  • Budi Santoso (NIK: 3201...)      │
   │  • Siti Nurhaliza (NIK: 3201...)    │
   └─────────────────────────────────────┘
   
   ATAU
   
   ┌─────────────────────────────────────┐
   │ Orang Tua Belum Terdaftar?          │
   │ [+ Tambah Data Orang Tua Baru]      │
   └─────────────────────────────────────┘
   
4. Jika PILIH orang tua existing:
   → Auto-fill: NIK Ayah, Nama Ayah, NIK Ibu, Nama Ibu, No. KK
   
5. Jika TAMBAH orang tua baru:
   → Isi manual: Nama Ayah, NIK Ayah, Nama Ibu, NIK Ibu, No. KK, Alamat
   
6. Isi data anak:
   ┌─────────────────────────────────────┐
   │ Nama Anak: [________]               │
   │ NIK Anak: [________]                │
   │ Tanggal Lahir: [__/__/____]         │
   │ Jenis Kelamin: (•) L  ( ) P         │
   │ No. Telepon Ortu: [________]        │
   │ Email Ortu: [________] (opsional)   │
   └─────────────────────────────────────┘
   
7. Submit → Data tersimpan:
   - Tabel anak (dengan posyandu_id)
   - Link ke orang tua (jika orang tua sudah punya akun)
```

### 2.4 Alur Input Pemeriksaan oleh Petugas (UPDATED)

**SEBELUM:**
```
1. Petugas pilih anak dari list semua anak
2. Input BB, TB, LK
3. Submit
```

**SESUDAH:**
```
1. Petugas pilih menu "Input Pemeriksaan"
2. Filter berdasarkan Posyandu (auto-select posyandu petugas):
   ┌─────────────────────────────────────┐
   │ Posyandu: Posyandu Melati 1 ✓       │
   │ (Otomatis terisi)                   │
   └─────────────────────────────────────┘
   
3. Cari Anak (Search dengan Autocomplete):
   ┌─────────────────────────────────────┐
   │ Cari Nama Anak atau Orang Tua:      │
   │ [________]                           │
   │                                      │
   │ Hasil:                               │
   │  • Ahmad (Ortu: Budi Santoso)       │
   │  • Siti (Ortu: Andi Wijaya)         │
   └─────────────────────────────────────┘
   
4. Pilih anak → Auto-fill data anak ditampilkan:
   ┌─────────────────────────────────────┐
   │ Nama: Ahmad Fauzi                   │
   │ Umur: 2 tahun 3 bulan               │
   │ Orang Tua: Budi Santoso             │
   │ Pemeriksaan Terakhir: 15 Feb 2025   │
   └─────────────────────────────────────┘
   
5. Input data pemeriksaan:
   ┌─────────────────────────────────────┐
   │ Tanggal: [__/__/____]               │
   │ Berat Badan: [___] kg               │
   │ Tinggi Badan: [___] cm              │
   │ Lingkar Kepala: [___] cm            │
   │ Catatan: [___________]              │
   └─────────────────────────────────────┘
   
6. Submit → AI deteksi stunting
7. Hasil ditampilkan + tersimpan
8. Notifikasi otomatis ke orang tua (jika ada akun)
```

---

## 3. FITUR DASHBOARD ORANG TUA

### 3.1 Dashboard Utama Orang Tua
**Route:** `/orang-tua/dashboard`

**Komponen:**
```
┌─────────────────────────────────────────────────────┐
│  Selamat Datang, [Nama Orang Tua]                  │
│                                                      │
│  ┌──────────────────┐  ┌──────────────────┐        │
│  │ Anak Terdaftar   │  │ Pemeriksaan      │        │
│  │      2           │  │  Terakhir        │        │
│  └──────────────────┘  │  15 Maret 2025   │        │
│                        └──────────────────┘        │
│                                                      │
│  Daftar Anak Saya:                                  │
│  ┌────────────────────────────────────────────┐    │
│  │ 📌 Ahmad Fauzi (L)                         │    │
│  │    Umur: 2 tahun 3 bulan                   │    │
│  │    Status: Normal ✓                        │    │
│  │    [Lihat Detail]                          │    │
│  ├────────────────────────────────────────────┤    │
│  │ 📌 Siti Aisyah (P)                         │    │
│  │    Umur: 4 tahun 1 bulan                   │    │
│  │    Status: Berisiko ⚠                      │    │
│  │    [Lihat Detail]                          │    │
│  └────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘
```

### 3.2 Detail Tumbuh Kembang Anak
**Route:** `/orang-tua/anak/:id`

**Komponen:**
```
┌─────────────────────────────────────────────────────┐
│  👶 Ahmad Fauzi                                     │
│  NIK: 3201012023010001                              │
│  Tanggal Lahir: 01 Januari 2023                    │
│  Umur: 2 tahun 3 bulan                             │
│  Posyandu: Posyandu Melati 1                       │
│                                                      │
│  Status Terkini: NORMAL ✓                          │
│                                                      │
│  ┌──────────────────────────────────────────────┐  │
│  │        Grafik Pertumbuhan                    │  │
│  │                                               │  │
│  │  TB (cm) ^                                   │  │
│  │    90 |           •                          │  │
│  │    85 |        •  •                          │  │
│  │    80 |     •  •                             │  │
│  │    75 |  •                                   │  │
│  │       +─────────────────────> Umur (bulan)  │  │
│  │       0   6   12  18  24  27               │  │
│  │                                               │  │
│  │  [Tab: BB] [Tab: TB] [Tab: LK]              │  │
│  └──────────────────────────────────────────────┘  │
│                                                      │
│  Riwayat Pemeriksaan:                               │
│  ┌────────────────────────────────────────────┐    │
│  │ 📅 15 Maret 2025                           │    │
│  │    BB: 12.5 kg | TB: 87 cm | LK: 48 cm     │    │
│  │    Status: Normal ✓                        │    │
│  │    Petugas: Ibu Sari (Posyandu Melati 1)   │    │
│  │    Catatan: Pertumbuhan sesuai usia        │    │
│  ├────────────────────────────────────────────┤    │
│  │ 📅 15 Februari 2025                        │    │
│  │    BB: 12.0 kg | TB: 85 cm | LK: 48 cm     │    │
│  │    Status: Normal ✓                        │    │
│  ├────────────────────────────────────────────┤    │
│  │ 📅 15 Januari 2025                         │    │
│  │    BB: 11.5 kg | TB: 83 cm | LK: 47 cm     │    │
│  │    Status: Normal ✓                        │    │
│  └────────────────────────────────────────────┘    │
│                                                      │
│  [Download Laporan PDF]                             │
└─────────────────────────────────────────────────────┘
```

### 3.3 Notifikasi & Reminder
**Route:** `/orang-tua/notifikasi`

**Fitur:**
- Notifikasi pemeriksaan baru
- Reminder jadwal pemeriksaan berikutnya
- Alert jika status anak berubah (misal jadi berisiko)
- Tips pencegahan stunting

---

## 4. UPDATE FITUR PETUGAS POSYANDU

### 4.1 Dashboard Petugas (Updated)
**Route:** `/admin/dashboard`

**Komponen Baru:**
```
┌─────────────────────────────────────────────────────┐
│  Dashboard Petugas - Posyandu Melati 1              │
│                                                      │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐         │
│  │ Total    │  │ Pemeriksaan│  │ Orang Tua │         │
│  │ Anak     │  │ Bulan Ini  │  │ Terdaftar │         │
│  │  45      │  │    23      │  │    38     │         │
│  └──────────┘  └──────────┘  └──────────┘         │
│                                                      │
│  [+ Tambah Anak Baru]  [+ Input Pemeriksaan]       │
│                                                      │
│  Pemeriksaan Hari Ini (15 Maret 2025):             │
│  ┌────────────────────────────────────────────┐    │
│  │ • Ahmad (Ortu: Budi) - Normal ✓            │    │
│  │ • Siti (Ortu: Ani) - Berisiko ⚠           │    │
│  └────────────────────────────────────────────┘    │
│                                                      │
│  Pending Verifikasi Orang Tua: 2                   │
│  [Lihat Detail]                                     │
└─────────────────────────────────────────────────────┘
```

### 4.2 Form Tambah Anak (Improved UX)
**Route:** `/admin/anak/tambah`

**UI/UX Flow:**
```javascript
// Step 1: Auto-select posyandu petugas
posyandu_id = current_user.posyandu_id; // Auto-filled

// Step 2: Search Orang Tua
<SearchInput 
  placeholder="Ketik nama orang tua..."
  onSearch={searchOrangTua}
  results={orangTuaList}
/>

// Step 3a: Jika orang tua existing dipilih
function onSelectOrangTua(orangTua) {
  autoFill({
    nama_ayah: orangTua.nama_ayah,
    nik_ayah: orangTua.nik_ayah,
    nama_ibu: orangTua.nama_ibu,
    nik_ibu: orangTua.nik_ibu,
    no_kk: orangTua.no_kk,
    alamat: orangTua.alamat,
    no_telepon: orangTua.no_telepon,
    email: orangTua.email
  });
}

// Step 3b: Jika tambah orang tua baru
<Button onClick={showFormOrangTuaBaru}>
  + Tambah Data Orang Tua Baru
</Button>

// Step 4: Form data anak (tetap manual input)
<Input name="nama_anak" />
<Input name="nik_anak" />
<DateInput name="tanggal_lahir" />
<Radio name="jenis_kelamin" options={['L', 'P']} />
```

### 4.3 Verifikasi Registrasi Orang Tua
**Route:** `/admin/verifikasi-orang-tua`

**Komponen:**
```
┌─────────────────────────────────────────────────────┐
│  Pending Verifikasi Orang Tua                       │
│                                                      │
│  ┌────────────────────────────────────────────┐    │
│  │ 👤 Budi Santoso                            │    │
│  │    NIK: 3201011980010001                   │    │
│  │    No. KK: 3201012012010001                │    │
│  │    Hubungan: Ayah                          │    │
│  │                                             │    │
│  │    Mendaftarkan untuk anak:                │    │
│  │    • Ahmad Fauzi (NIK: 3201012023010001)   │    │
│  │      ✓ Data anak ditemukan di database     │    │
│  │      ✓ No. KK cocok                        │    │
│  │                                             │    │
│  │    Email: budi@email.com                   │    │
│  │    Telepon: 081234567890                   │    │
│  │    Tanggal Daftar: 14 Maret 2025           │    │
│  │                                             │    │
│  │    [✓ Setujui]  [✗ Tolak]                  │    │
│  └────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘
```

---

## 5. UPDATE API ENDPOINTS

### 5.1 Public Endpoints (Updated)
```
POST   /api/register-petugas          - Registrasi petugas
POST   /api/register-orang-tua        - Registrasi orang tua (NEW)
POST   /api/login                     - Login (super_admin/petugas/orang_tua)
GET    /api/public/info-stunting      - Info stunting
GET    /api/public/posyandu           - List posyandu untuk dropdown (NEW)
```

### 5.2 Orang Tua Endpoints (NEW)
```
GET    /api/orang-tua/dashboard/stats       - Statistik dashboard
GET    /api/orang-tua/profile               - Profile orang tua
PUT    /api/orang-tua/profile               - Update profile
GET    /api/orang-tua/anak                  - List anak orang tua ini saja
GET    /api/orang-tua/anak/:id              - Detail anak (validate ownership)
GET    /api/orang-tua/anak/:id/pemeriksaan  - Riwayat pemeriksaan anak
GET    /api/orang-tua/anak/:id/grafik       - Data grafik pertumbuhan
GET    /api/orang-tua/notifikasi            - Notifikasi orang tua
POST   /api/orang-tua/anak/:id/download-pdf - Download laporan PDF
```

### 5.3 Petugas Endpoints (Updated)
```
GET    /api/petugas/dashboard/stats         - Statistik (updated)
GET    /api/petugas/posyandu/:id/anak       - List anak di posyandu tertentu (NEW)
GET    /api/petugas/search-orang-tua        - Search orang tua (autocomplete) (NEW)
POST   /api/petugas/orang-tua               - Tambah data orang tua baru (NEW)
GET    /api/petugas/anak                    - List anak (filtered by posyandu)
POST   /api/petugas/anak                    - Tambah anak (improved)
PUT    /api/petugas/anak/:id                - Edit anak
DELETE /api/petugas/anak/:id                - Hapus anak
GET    /api/petugas/pemeriksaan             - List pemeriksaan
POST   /api/petugas/pemeriksaan             - Tambah pemeriksaan
GET    /api/petugas/verifikasi-orang-tua    - List pending verifikasi (NEW)
POST   /api/petugas/verifikasi-orang-tua/:id/approve  - Approve (NEW)
POST   /api/petugas/verifikasi-orang-tua/:id/reject   - Reject (NEW)
```

### 5.4 Super Admin Endpoints (Updated)
```
GET    /api/super-admin/posyandu            - CRUD master posyandu (NEW)
POST   /api/super-admin/posyandu            - Create posyandu (NEW)
PUT    /api/super-admin/posyandu/:id        - Update posyandu (NEW)
DELETE /api/super-admin/posyandu/:id        - Delete posyandu (NEW)
GET    /api/super-admin/petugas             - List petugas
GET    /api/super-admin/orang-tua           - List semua orang tua (NEW)
GET    /api/super-admin/laporan/global      - Laporan global
```

---

## 6. SECURITY & PRIVACY

### 6.1 Access Control Rules

#### Middleware untuk Orang Tua
```php
// IsOrangTua.php
public function handle($request, Closure $next)
{
    if (auth()->user()->role !== 'orang_tua') {
        abort(403, 'Unauthorized');
    }
    
    if (auth()->user()->status !== 'active') {
        return redirect('/orang-tua/pending');
    }
    
    return $next($request);
}
```

#### Middleware untuk Ownership Check
```php
// CheckAnakOwnership.php
public function handle($request, Closure $next, $anakId)
{
    $user = auth()->user();
    
    if ($user->role === 'orang_tua') {
        // Cek apakah anak ini milik orang tua ini
        $hasAccess = DB::table('orang_tua_anak')
            ->join('orang_tua_profile', 'orang_tua_anak.orang_tua_id', '=', 'orang_tua_profile.id')
            ->where('orang_tua_profile.user_id', $user->id)
            ->where('orang_tua_anak.anak_id', $anakId)
            ->exists();
        
        if (!$hasAccess) {
            abort(403, 'You do not have access to this child data');
        }
    }
    
    return $next($request);
}
```

### 6.2 Data Privacy Rules

#### Query Scopes untuk Orang Tua
```php
// Anak Model
public function scopeForOrangTua($query, $userId)
{
    return $query->whereHas('orangTuaRelations', function($q) use ($userId) {
        $q->whereHas('orangTuaProfile', function($q2) use ($userId) {
            $q2->where('user_id', $userId);
        });
    });
}

// Usage:
$anakList = Anak::forOrangTua(auth()->id())->get();
```

#### API Response Filtering
```php
// AnakController untuk Orang Tua
public function index()
{
    $user = auth()->user();
    
    // HANYA ambil anak yang terhubung dengan orang tua ini
    $anak = Anak::forOrangTua($user->id)
        ->with(['pemeriksaan' => function($q) {
            $q->latest()->limit(5);
        }])
        ->get();
    
    return response()->json($anak);
}

public function show($id)
{
    $user = auth()->user();
    
    // Validate ownership
    $anak = Anak::forOrangTua($user->id)
        ->where('id', $id)
        ->firstOrFail(); // 404 jika bukan anak mereka
    
    return response()->json($anak);
}
```

---

## 7. VALIDATION LOGIC

### 7.1 Validasi Registrasi Orang Tua

```php
// RegisterOrangTuaRequest.php
public function rules()
{
    return [
        'username' => 'required|unique:users',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'nama_lengkap' => 'required|max:200',
        'nik' => 'required|digits:16|unique:orang_tua_profile',
        'no_kk' => 'required|digits:16',
        'hubungan' => 'required|in:ayah,ibu,wali',
        'no_telepon' => 'required|max:15',
        'nik_anak' => 'required|digits:16|exists:anak,nik_anak',
        'nama_anak' => 'required',
    ];
}

// Custom validation
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Validasi: NIK anak dan nama anak harus cocok
        $anak = Anak::where('nik_anak', $this->nik_anak)
            ->where('nama', 'LIKE', '%' . $this->nama_anak . '%')
            ->first();
        
        if (!$anak) {
            $validator->errors()->add('nik_anak', 
                'Data anak tidak ditemukan. Pastikan NIK dan nama anak benar.');
            return;
        }
        
        // Validasi: No. KK harus cocok
        if ($anak->no_kk !== $this->no_kk) {
            $validator->errors()->add('no_kk', 
                'Nomor KK tidak cocok dengan data anak di database.');
            return;
        }
        
        // Validasi: NIK orang tua belum terdaftar untuk anak ini
        $existing = DB::table('orang_tua_anak')
            ->join('orang_tua_profile', 'orang_tua_anak.orang_tua_id', '=', 'orang_tua_profile.id')
            ->where('orang_tua_anak.anak_id', $anak->id)
            ->where('orang_tua_profile.nik', $this->nik)
            ->exists();
        
        if ($existing) {
            $validator->errors()->add('nik', 
                'NIK ini sudah terdaftar untuk anak tersebut.');
        }
    });
}
```

### 7.2 Validasi Input Anak oleh Petugas

```php
// StoreAnakRequest.php
public function rules()
{
    return [
        'nama' => 'required|max:200',
        'nik_anak' => 'required|digits:16|unique:anak',
        'tanggal_lahir' => 'required|date|before:today',
        'jenis_kelamin' => 'required|in:L,P',
        'no_kk' => 'required|digits:16',
        'nama_ayah' => 'nullable|max:200',
        'nik_ayah' => 'nullable|digits:16',
        'nama_ibu' => 'nullable|max:200',
        'nik_ibu' => 'nullable|digits:16',
        'no_telepon_ortu' => 'nullable|max:15',
        'email_ortu' => 'nullable|email',
        'alamat' => 'nullable',
        'posyandu_id' => 'required|exists:posyandu,id',
    ];
}

// Custom validation
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Minimal harus ada nama ayah atau ibu
        if (!$this->nama_ayah && !$this->nama_ibu) {
            $validator->errors()->add('nama_ayah', 
                'Minimal harus mengisi nama ayah atau nama ibu.');
        }
    });
}
```

---

## 8. MASTER DATA POSYANDU

### 8.1 Seeder untuk Posyandu

```php
// PosyanduSeeder.php
public function run()
{
    $posyandu = [
        [
            'nama' => 'Posyandu Melati 1',
            'kode_posyandu' => 'POS-MLT-001',
            'alamat' => 'Jl. Melati No. 10, RT 01/RW 02',
            'kelurahan' => 'Denpasar Barat',
            'kecamatan' => 'Denpasar',
            'kota' => 'Denpasar',
            'provinsi' => 'Bali',
            'latitude' => -8.670458,
            'longitude' => 115.212631,
            'no_telepon' => '0361123456',
            'jadwal_buka' => json_encode([
                'senin' => '08:00-12:00',
                'rabu' => '08:00-12:00',
                'jumat' => '08:00-12:00'
            ]),
            'status' => 'active',
        ],
        [
            'nama' => 'Posyandu Anggrek',
            'kode_posyandu' => 'POS-AGR-002',
            'alamat' => 'Jl. Anggrek No. 5, RT 03/RW 01',
            'kelurahan' => 'Denpasar Timur',
            'kecamatan' => 'Denpasar',
            'kota' => 'Denpasar',
            'provinsi' => 'Bali',
            'latitude' => -8.663395,
            'longitude' => 115.226250,
            'no_telepon' => '0361234567',
            'jadwal_buka' => json_encode([
                'selasa' => '08:00-12:00',
                'kamis' => '08:00-12:00',
            ]),
            'status' => 'active',
        ],
        // Tambahkan posyandu lainnya
    ];
    
    foreach ($posyandu as $p) {
        DB::table('posyandu')->insert($p);
    }
}
```

### 8.2 Super Admin - CRUD Posyandu

**Route:** `/super-admin/posyandu`

**UI:**
```
┌─────────────────────────────────────────────────────┐
│  Master Data Posyandu                               │
│                                                      │
│  [+ Tambah Posyandu Baru]                           │
│                                                      │
│  ┌────────────────────────────────────────────┐    │
│  │ Posyandu Melati 1                          │    │
│  │ Kode: POS-MLT-001                          │    │
│  │ Alamat: Jl. Melati No. 10, Denpasar        │    │
│  │ Petugas: 3 orang                           │    │
│  │ Status: Active ✓                           │    │
│  │ [Edit] [Lihat Detail] [Nonaktifkan]       │    │
│  ├────────────────────────────────────────────┤    │
│  │ Posyandu Anggrek                           │    │
│  │ Kode: POS-AGR-002                          │    │
│  │ Alamat: Jl. Anggrek No. 5, Denpasar        │    │
│  │ Petugas: 2 orang                           │    │
│  │ Status: Active ✓                           │    │
│  │ [Edit] [Lihat Detail] [Nonaktifkan]       │    │
│  └────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘
```

---

## 9. NOTIFICATION SYSTEM

### 9.1 Notifikasi untuk Orang Tua

**Trigger Event:**
```php
// Event: PemeriksaanCreated
class PemeriksaanCreated
{
    public $pemeriksaan;
    
    public function __construct(Pemeriksaan $pemeriksaan)
    {
        $this->pemeriksaan = $pemeriksaan;
    }
}

// Listener: NotifyOrangTua
class NotifyOrangTua
{
    public function handle(PemeriksaanCreated $event)
    {
        $pemeriksaan = $event->pemeriksaan;
        $anak = $pemeriksaan->anak;
        
        // Ambil orang tua yang terhubung dengan anak ini
        $orangTuaList = $anak->orangTuaRelations()
            ->with('orangTuaProfile.user')
            ->get();
        
        foreach ($orangTuaList as $relation) {
            $user = $relation->orangTuaProfile->user;
            
            // Kirim email
            Mail::to($user->email)->send(new PemeriksaanBaru($pemeriksaan, $anak));
            
            // Simpan notifikasi in-app
            Notification::create([
                'user_id' => $user->id,
                'type' => 'pemeriksaan_baru',
                'title' => 'Pemeriksaan Baru untuk ' . $anak->nama,
                'message' => 'Pemeriksaan tanggal ' . $pemeriksaan->tanggal_pemeriksaan,
                'data' => json_encode([
                    'pemeriksaan_id' => $pemeriksaan->id,
                    'anak_id' => $anak->id,
                    'hasil' => $pemeriksaan->hasil_deteksi
                ]),
                'read_at' => null,
            ]);
            
            // Jika hasilnya berisiko/stunting, kirim alert
            if (in_array($pemeriksaan->hasil_deteksi, ['berisiko', 'stunting'])) {
                // Kirim SMS atau WhatsApp (optional)
                // SMS::send($user->no_telepon, "ALERT: ...");
            }
        }
    }
}
```

### 9.2 Tabel Notifikasi

```sql
CREATE TABLE notifications (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  type VARCHAR(50) NOT NULL, -- 'pemeriksaan_baru', 'status_changed', 'reminder'
  title VARCHAR(200) NOT NULL,
  message TEXT,
  data JSONB, -- Data tambahan
  read_at TIMESTAMP,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notif_user ON notifications(user_id);
CREATE INDEX idx_notif_read ON notifications(read_at);
CREATE INDEX idx_notif_created ON notifications(created_at);
```

---

## 10. IMPLEMENTATION TIMELINE

### Phase 1: Database & Backend Core (Week 1-2)
- [ ] Update database schema (users, posyandu, anak, orang_tua_profile, dll)
- [ ] Buat migrations untuk semua tabel baru
- [ ] Buat seeders (posyandu, update role)
- [ ] Update User model & relationships
- [ ] Buat model: Posyandu, OrangTuaProfile, OrangTuaAnak
- [ ] Buat middleware: IsOrangTua, CheckAnakOwnership
- [ ] Setup authentication untuk role orang_tua

### Phase 2: API Orang Tua (Week 2-3)
- [ ] API registrasi orang tua dengan validasi
- [ ] API login untuk role orang_tua
- [ ] API dashboard orang tua
- [ ] API list anak (with ownership check)
- [ ] API detail anak & riwayat pemeriksaan
- [ ] API grafik pertumbuhan
- [ ] API download laporan PDF

### Phase 3: API Petugas (Improvement) (Week 3-4)
- [ ] API master posyandu (dropdown)
- [ ] API search orang tua (autocomplete)
- [ ] API tambah orang tua baru
- [ ] Update API tambah anak (with auto-fill)
- [ ] Update API input pemeriksaan (with search)
- [ ] API verifikasi registrasi orang tua
- [ ] API approve/reject orang tua

### Phase 4: Frontend Orang Tua (Week 4-5)
- [ ] Halaman registrasi orang tua
- [ ] Halaman login orang tua
- [ ] Dashboard orang tua
- [ ] Halaman detail anak
- [ ] Grafik pertumbuhan (Chart.js/Recharts)
- [ ] Halaman notifikasi
- [ ] Responsive mobile

### Phase 5: Frontend Petugas (Improvement) (Week 5-6)
- [ ] Update form tambah anak (dropdown posyandu)
- [ ] Implementasi search orang tua (autocomplete)
- [ ] Auto-fill data orang tua
- [ ] Update form input pemeriksaan (search anak)
- [ ] Halaman verifikasi orang tua
- [ ] Approve/Reject modal

### Phase 6: Super Admin (Master Posyandu) (Week 6)
- [ ] CRUD posyandu (create, read, update, delete)
- [ ] Halaman master posyandu
- [ ] Form tambah/edit posyandu
- [ ] Import posyandu dari CSV/Excel (optional)

### Phase 7: Notification System (Week 7)
- [ ] Setup email notification
- [ ] Event & Listener untuk pemeriksaan baru
- [ ] In-app notification
- [ ] Notification badge
- [ ] Mark as read functionality

### Phase 8: Testing & Deployment (Week 8)
- [ ] Unit testing
- [ ] Integration testing
- [ ] UAT dengan super admin, petugas, orang tua
- [ ] Bug fixing
- [ ] Security audit
- [ ] Performance optimization
- [ ] Deployment

---

## 11. LARAVEL SPECIFIC IMPLEMENTATION

### 11.1 Models & Relationships

#### User Model (Updated)
```php
// app/Models/User.php
class User extends Authenticatable
{
    protected $fillable = [
        'username', 'email', 'password', 'role', 'status'
    ];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    // Relationships
    public function petugasProfile()
    {
        return $this->hasOne(PetugasProfile::class);
    }
    
    public function orangTuaProfile()
    {
        return $this->hasOne(OrangTuaProfile::class);
    }
    
    // Helper methods
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }
    
    public function isPetugasPosyandu()
    {
        return $this->role === 'petugas_posyandu';
    }
    
    public function isOrangTua()
    {
        return $this->role === 'orang_tua';
    }
    
    public function isActive()
    {
        return $this->status === 'active';
    }
}
```

#### Posyandu Model (NEW)
```php
// app/Models/Posyandu.php
class Posyandu extends Model
{
    protected $table = 'posyandu';
    
    protected $fillable = [
        'nama', 'kode_posyandu', 'alamat', 'kelurahan', 
        'kecamatan', 'kota', 'provinsi', 'latitude', 
        'longitude', 'no_telepon', 'jadwal_buka', 'status'
    ];
    
    protected $casts = [
        'jadwal_buka' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    // Relationships
    public function petugas()
    {
        return $this->hasMany(PetugasProfile::class);
    }
    
    public function anak()
    {
        return $this->hasMany(Anak::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

#### OrangTuaProfile Model (NEW)
```php
// app/Models/OrangTuaProfile.php
class OrangTuaProfile extends Model
{
    protected $table = 'orang_tua_profile';
    
    protected $fillable = [
        'user_id', 'nama_lengkap', 'nik', 'no_kk', 
        'hubungan', 'no_telepon', 'alamat'
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function anakRelations()
    {
        return $this->hasMany(OrangTuaAnak::class, 'orang_tua_id');
    }
    
    public function anak()
    {
        return $this->belongsToMany(
            Anak::class, 
            'orang_tua_anak', 
            'orang_tua_id', 
            'anak_id'
        )->withPivot('hubungan', 'is_primary', 'verified_at');
    }
}
```

#### Anak Model (Updated)
```php
// app/Models/Anak.php
class Anak extends Model
{
    protected $table = 'anak';
    
    protected $fillable = [
        'nama', 'nik_anak', 'tanggal_lahir', 'jenis_kelamin',
        'no_kk', 'nama_ayah', 'nik_ayah', 'nama_ibu', 'nik_ibu',
        'no_telepon_ortu', 'email_ortu', 'alamat',
        'petugas_id', 'posyandu_id'
    ];
    
    protected $casts = [
        'tanggal_lahir' => 'date',
    ];
    
    // Relationships
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
    
    public function posyandu()
    {
        return $this->belongsTo(Posyandu::class);
    }
    
    public function pemeriksaan()
    {
        return $this->hasMany(Pemeriksaan::class);
    }
    
    public function orangTuaRelations()
    {
        return $this->hasMany(OrangTuaAnak::class, 'anak_id');
    }
    
    public function orangTua()
    {
        return $this->belongsToMany(
            OrangTuaProfile::class,
            'orang_tua_anak',
            'anak_id',
            'orang_tua_id'
        )->withPivot('hubungan', 'is_primary');
    }
    
    // Scopes
    public function scopeForOrangTua($query, $userId)
    {
        return $query->whereHas('orangTuaRelations', function($q) use ($userId) {
            $q->whereHas('orangTuaProfile', function($q2) use ($userId) {
                $q2->where('user_id', $userId);
            });
        });
    }
    
    public function scopeForPosyandu($query, $posyanduId)
    {
        return $query->where('posyandu_id', $posyanduId);
    }
    
    // Accessors
    public function getUmurAttribute()
    {
        $now = now();
        $lahir = $this->tanggal_lahir;
        
        $years = $now->diffInYears($lahir);
        $months = $now->copy()->subYears($years)->diffInMonths($lahir);
        
        return [
            'years' => $years,
            'months' => $months,
            'total_months' => ($years * 12) + $months,
            'formatted' => "{$years} tahun {$months} bulan"
        ];
    }
}
```

#### OrangTuaAnak Model (NEW - Pivot)
```php
// app/Models/OrangTuaAnak.php
class OrangTuaAnak extends Model
{
    protected $table = 'orang_tua_anak';
    
    protected $fillable = [
        'orang_tua_id', 'anak_id', 'hubungan', 
        'is_primary', 'verified_at', 'verified_by'
    ];
    
    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
    ];
    
    public $timestamps = false;
    
    // Relationships
    public function orangTuaProfile()
    {
        return $this->belongsTo(OrangTuaProfile::class, 'orang_tua_id');
    }
    
    public function anak()
    {
        return $this->belongsTo(Anak::class);
    }
    
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
```

### 11.2 Controllers

#### OrangTuaController (NEW)
```php
// app/Http/Controllers/OrangTuaController.php
class OrangTuaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:orang_tua', 'status:active']);
    }
    
    public function dashboard()
    {
        $user = auth()->user();
        
        $stats = [
            'total_anak' => Anak::forOrangTua($user->id)->count(),
            'pemeriksaan_bulan_ini' => Pemeriksaan::whereHas('anak', function($q) use ($user) {
                $q->forOrangTua($user->id);
            })->whereMonth('tanggal_pemeriksaan', now()->month)->count(),
        ];
        
        return response()->json($stats);
    }
    
    public function getAnak()
    {
        $user = auth()->user();
        
        $anak = Anak::forOrangTua($user->id)
            ->with(['pemeriksaan' => function($q) {
                $q->latest()->first();
            }, 'posyandu'])
            ->get();
        
        return response()->json($anak);
    }
    
    public function showAnak($id)
    {
        $user = auth()->user();
        
        $anak = Anak::forOrangTua($user->id)
            ->with(['pemeriksaan', 'posyandu', 'petugas'])
            ->findOrFail($id);
        
        return response()->json($anak);
    }
    
    public function getPemeriksaanAnak($id)
    {
        $user = auth()->user();
        
        // Validate ownership
        $anak = Anak::forOrangTua($user->id)->findOrFail($id);
        
        $pemeriksaan = $anak->pemeriksaan()
            ->with('petugas')
            ->orderBy('tanggal_pemeriksaan', 'desc')
            ->get();
        
        return response()->json($pemeriksaan);
    }
    
    public function getGrafikAnak($id)
    {
        $user = auth()->user();
        
        $anak = Anak::forOrangTua($user->id)->findOrFail($id);
        
        $data = $anak->pemeriksaan()
            ->orderBy('tanggal_pemeriksaan', 'asc')
            ->get()
            ->map(function($p) {
                return [
                    'tanggal' => $p->tanggal_pemeriksaan->format('Y-m-d'),
                    'berat_badan' => $p->berat_badan,
                    'tinggi_badan' => $p->tinggi_badan,
                    'lingkar_kepala' => $p->lingkar_kepala,
                    'hasil' => $p->hasil_deteksi,
                ];
            });
        
        return response()->json($data);
    }
}
```

#### PetugasController (Updated)
```php
// app/Http/Controllers/PetugasController.php - UPDATED METHODS

public function searchOrangTua(Request $request)
{
    $query = $request->input('q');
    $posyanduId = auth()->user()->petugasProfile->posyandu_id;
    
    // Search di data anak yang ada di posyandu ini
    $results = Anak::where('posyandu_id', $posyanduId)
        ->where(function($q) use ($query) {
            $q->where('nama_ayah', 'LIKE', "%{$query}%")
              ->orWhere('nama_ibu', 'LIKE', "%{$query}%");
        })
        ->select('nama_ayah', 'nik_ayah', 'nama_ibu', 'nik_ibu', 'no_kk', 'alamat', 'no_telepon_ortu', 'email_ortu')
        ->distinct()
        ->limit(10)
        ->get()
        ->map(function($item) {
            return [
                'nama_ayah' => $item->nama_ayah,
                'nik_ayah' => $item->nik_ayah,
                'nama_ibu' => $item->nama_ibu,
                'nik_ibu' => $item->nik_ibu,
                'no_kk' => $item->no_kk,
                'alamat' => $item->alamat,
                'no_telepon' => $item->no_telepon_ortu,
                'email' => $item->email_ortu,
            ];
        });
    
    return response()->json($results);
}

public function getPendingVerifikasi()
{
    $posyanduId = auth()->user()->petugasProfile->posyandu_id;
    
    $pending = User::where('role', 'orang_tua')
        ->where('status', 'pending')
        ->whereHas('orangTuaProfile.anakRelations.anak', function($q) use ($posyanduId) {
            $q->where('posyandu_id', $posyanduId);
        })
        ->with(['orangTuaProfile', 'orangTuaProfile.anakRelations.anak'])
        ->get();
    
    return response()->json($pending);
}

public function approveOrangTua($id)
{
    $user = User::findOrFail($id);
    
    DB::transaction(function() use ($user) {
        $user->update(['status' => 'active']);
        
        // Update verified_at di relasi
        $user->orangTuaProfile->anakRelations()->update([
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);
        
        // Kirim email notifikasi
        Mail::to($user->email)->send(new OrangTuaApproved($user));
    });
    
    return response()->json(['message' => 'Orang tua berhasil diverifikasi']);
}
```

---

## 12. FRONTEND COMPONENTS (React/Vue)

### 12.1 Registrasi Orang Tua Form

```jsx
// RegisterOrangTua.jsx
import { useState } from 'react';

export default function RegisterOrangTua() {
  const [formData, setFormData] = useState({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    nama_lengkap: '',
    nik: '',
    no_kk: '',
    hubungan: 'ayah',
    no_telepon: '',
    nik_anak: '',
    nama_anak: '',
  });
  
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});
    
    try {
      const response = await fetch('/api/register-orang-tua', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });
      
      const data = await response.json();
      
      if (!response.ok) {
        setErrors(data.errors || {});
        return;
      }
      
      // Success
      alert('Registrasi berhasil! Silakan tunggu verifikasi dari petugas posyandu.');
      window.location.href = '/login';
      
    } catch (error) {
      alert('Terjadi kesalahan. Silakan coba lagi.');
    } finally {
      setLoading(false);
    }
  };
  
  return (
    <div className="max-w-2xl mx-auto p-6">
      <h1 className="text-2xl font-bold mb-6">Registrasi Orang Tua</h1>
      
      <form onSubmit={handleSubmit} className="space-y-4">
        {/* Account Info */}
        <div className="border-b pb-4">
          <h2 className="font-semibold mb-3">Informasi Akun</h2>
          
          <input
            type="text"
            placeholder="Username"
            value={formData.username}
            onChange={(e) => setFormData({...formData, username: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          />
          {errors.username && <p className="text-red-500 text-sm">{errors.username[0]}</p>}
          
          <input
            type="email"
            placeholder="Email"
            value={formData.email}
            onChange={(e) => setFormData({...formData, email: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          />
          {errors.email && <p className="text-red-500 text-sm">{errors.email[0]}</p>}
          
          <input
            type="password"
            placeholder="Password"
            value={formData.password}
            onChange={(e) => setFormData({...formData, password: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          />
          
          <input
            type="password"
            placeholder="Konfirmasi Password"
            value={formData.password_confirmation}
            onChange={(e) => setFormData({...formData, password_confirmation: e.target.value})}
            className="w-full p-2 border rounded"
          />
          {errors.password && <p className="text-red-500 text-sm">{errors.password[0]}</p>}
        </div>
        
        {/* Parent Info */}
        <div className="border-b pb-4">
          <h2 className="font-semibold mb-3">Data Orang Tua</h2>
          
          <input
            type="text"
            placeholder="Nama Lengkap"
            value={formData.nama_lengkap}
            onChange={(e) => setFormData({...formData, nama_lengkap: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          />
          {errors.nama_lengkap && <p className="text-red-500 text-sm">{errors.nama_lengkap[0]}</p>}
          
          <input
            type="text"
            placeholder="NIK (16 digit)"
            maxLength="16"
            value={formData.nik}
            onChange={(e) => setFormData({...formData, nik: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          />
          {errors.nik && <p className="text-red-500 text-sm">{errors.nik[0]}</p>}
          
          <input
            type="text"
            placeholder="No. Kartu Keluarga (16 digit)"
            maxLength="16"
            value={formData.no_kk}
            onChange={(e) => setFormData({...formData, no_kk: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          />
          {errors.no_kk && <p className="text-red-500 text-sm">{errors.no_kk[0]}</p>}
          
          <select
            value={formData.hubungan}
            onChange={(e) => setFormData({...formData, hubungan: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          >
            <option value="ayah">Ayah</option>
            <option value="ibu">Ibu</option>
            <option value="wali">Wali</option>
          </select>
          
          <input
            type="text"
            placeholder="No. Telepon"
            value={formData.no_telepon}
            onChange={(e) => setFormData({...formData, no_telepon: e.target.value})}
            className="w-full p-2 border rounded"
          />
          {errors.no_telepon && <p className="text-red-500 text-sm">{errors.no_telepon[0]}</p>}
        </div>
        
        {/* Child Validation */}
        <div className="border-b pb-4">
          <h2 className="font-semibold mb-3">Validasi Data Anak</h2>
          <p className="text-sm text-gray-600 mb-3">
            Masukkan NIK dan nama anak yang sudah terdaftar di posyandu untuk validasi
          </p>
          
          <input
            type="text"
            placeholder="NIK Anak (16 digit)"
            maxLength="16"
            value={formData.nik_anak}
            onChange={(e) => setFormData({...formData, nik_anak: e.target.value})}
            className="w-full p-2 border rounded mb-2"
          />
          {errors.nik_anak && <p className="text-red-500 text-sm">{errors.nik_anak[0]}</p>}
          
          <input
            type="text"
            placeholder="Nama Anak"
            value={formData.nama_anak}
            onChange={(e) => setFormData({...formData, nama_anak: e.target.value})}
            className="w-full p-2 border rounded"
          />
          {errors.nama_anak && <p className="text-red-500 text-sm">{errors.nama_anak[0]}</p>}
        </div>
        
        <button
          type="submit"
          disabled={loading}
          className="w-full bg-blue-600 text-white py-3 rounded font-semibold hover:bg-blue-700 disabled:bg-gray-400"
        >
          {loading ? 'Memproses...' : 'Daftar'}
        </button>
      </form>
      
      <p className="mt-4 text-center text-sm">
        Sudah punya akun? <a href="/login" className="text-blue-600">Login di sini</a>
      </p>
    </div>
  );
}
```

### 12.2 Autocomplete Search Orang Tua

```jsx
// SearchOrangTua.jsx
import { useState, useEffect } from 'react';

export default function SearchOrangTua({ onSelect }) {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState([]);
  const [loading, setLoading] = useState(false);
  
  useEffect(() => {
    if (query.length < 3) {
      setResults([]);
      return;
    }
    
    const timer = setTimeout(async () => {
      setLoading(true);
      try {
        const response = await fetch(`/api/petugas/search-orang-tua?q=${query}`);
        const data = await response.json();
        setResults(data);
      } catch (error) {
        console.error(error);
      } finally {
        setLoading(false);
      }
    }, 300);
    
    return () => clearTimeout(timer);
  }, [query]);
  
  return (
    <div className="relative">
      <input
        type="text"
        placeholder="Ketik nama ayah atau ibu..."
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        className="w-full p-3 border rounded"
      />
      
      {loading && (
        <div className="absolute top-full mt-1 w-full bg-white border rounded p-3">
          Loading...
        </div>
      )}
      
      {results.length > 0 && (
        <div className="absolute top-full mt-1 w-full bg-white border rounded shadow-lg max-h-64 overflow-y-auto z-10">
          {results.map((ortu, index) => (
            <button
              key={index}
              onClick={() => {
                onSelect(ortu);
                setQuery('');
                setResults([]);
              }}
              className="w-full text-left p-3 hover:bg-gray-100 border-b last:border-b-0"
            >
              <div className="font-semibold">
                {ortu.nama_ayah && `Ayah: ${ortu.nama_ayah}`}
                {ortu.nama_ayah && ortu.nama_ibu && ' | '}
                {ortu.nama_ibu && `Ibu: ${ortu.nama_ibu}`}
              </div>
              <div className="text-sm text-gray-600">
                NIK Ayah: {ortu.nik_ayah || '-'} | NIK Ibu: {ortu.nik_ibu || '-'}
              </div>
              <div className="text-sm text-gray-600">
                No. KK: {ortu.no_kk}
              </div>
            </button>
          ))}
        </div>
      )}
      
      {query.length >= 3 && !loading && results.length === 0 && (
        <div className="absolute top-full mt-1 w-full bg-white border rounded p-3 text-gray-500">
          Tidak ada hasil. 
          <button 
            onClick={() => onSelect(null)}
            className="text-blue-600 ml-2"
          >
            Tambah data orang tua baru
          </button>
        </div>
      )}
    </div>
  );
}
```

---

## 13. SECURITY CHECKLIST

- [ ] Role-based access control (RBAC) implemented
- [ ] Ownership validation untuk orang tua
- [ ] Input sanitization & validation
- [ ] SQL injection protection (Eloquent ORM)
- [ ] XSS protection
- [ ] CSRF protection
- [ ] Password hashing (bcrypt)
- [ ] Rate limiting on registration
- [ ] Email verification (optional)
- [ ] Audit logging untuk super admin actions

---

## 14. TESTING SCENARIOS

### Test Case 1: Registrasi Orang Tua Berhasil
```
1. Orang tua isi form registrasi lengkap
2. NIK anak & nama anak cocok dengan database
3. No. KK cocok
4. Status = PENDING
5. Email notifikasi terkirim
6. Petugas terima notifikasi pending verifikasi
```

### Test Case 2: Registrasi Gagal - NIK Anak Tidak Ditemukan
```
1. Orang tua input NIK anak yang tidak ada di database
2. Validasi gagal
3. Error: "Data anak tidak ditemukan"
```

### Test Case 3: Orang Tua Hanya Lihat Data Anaknya Sendiri
```
1. Orang tua A login
2. Request GET /api/orang-tua/anak
3. Response hanya anak yang terhubung dengan orang tua A
4. Request GET /api/orang-tua/anak/{id_anak_orang_lain}
5. Response: 403 Forbidden atau 404 Not Found
```

### Test Case 4: Petugas Input Anak dengan Auto-fill
```
1. Petugas pilih posyandu (auto-select)
2. Petugas search "Budi"
3. Dropdown muncul dengan hasil
4. Petugas pilih "Budi Santoso"
5. Form auto-fill: NIK ayah, nama ayah, dll
6. Petugas isi data anak
7. Submit berhasil
```

---

## 15. FUTURE ENHANCEMENTS

- [ ] Mobile app untuk orang tua (React Native/Flutter)
- [ ] Push notification (FCM/APNs)
- [ ] WhatsApp notification integration
- [ ] Export grafik pertumbuhan ke PDF
- [ ] Chatbot untuk konsultasi stunting
- [ ] Appointment booking untuk pemeriksaan
- [ ] Reminder otomatis jadwal pemeriksaan
- [ ] Multi-language support (Bahasa/English)
- [ ] Integrasi dengan sistem e-Posyandu Kemenkes

---

**Version:** 2.0  
**Created:** 2025-04-02  
**Last Updated:** 2025-04-02  
**Status:** Ready for Implementation  
**Estimated Timeline:** 8 Weeks
