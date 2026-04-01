# Plan Pengembangan AI Stunt Detect System

## Overview
Pengembangan sistem AI Stunt Detect dengan penambahan:
1. Landing page publik (tanpa login)
2. Role Super Admin untuk manajemen petugas posyandu
3. Sistem registrasi untuk petugas posyandu dengan verifikasi

---

## 1. ARSITEKTUR SISTEM

### 1.1 Role & Permissions

#### Super Admin
- **Akses**: Full control sistem
- **Fungsi**:
  - Verifikasi/approve registrasi petugas posyandu
  - Reject registrasi dengan alasan
  - Lihat daftar semua petugas posyandu (aktif/pending/ditolak)
  - Edit/suspend/hapus akun petugas posyandu
  - Lihat statistik dan laporan global
  - Kelola data master (jika ada)

#### Petugas Posyandu (Admin)
- **Akses**: Setelah diverifikasi oleh Super Admin
- **Fungsi**:
  - Login ke dashboard
  - Melakukan pencatatan data anak (deteksi stunting)
  - Lihat data anak yang sudah dicatat
  - Update data pemeriksaan
  - Generate laporan posyandu

#### Masyarakat Umum
- **Akses**: Landing page (tanpa login)
- **Fungsi**:
  - Lihat informasi tentang stunting
  - Lihat informasi layanan posyandu
  - Kalkulator pertumbuhan anak (opsional)
  - Hubungi/lokasi posyandu terdekat

---

## 2. DATABASE SCHEMA

### 2.1 Tabel Users (Update)
```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(100) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin', 'petugas_posyandu') NOT NULL,
  status ENUM('pending', 'active', 'rejected', 'suspended') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2.2 Tabel Petugas Posyandu Profile
```sql
CREATE TABLE petugas_profile (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  nama_lengkap VARCHAR(200) NOT NULL,
  nik VARCHAR(16) UNIQUE NOT NULL,
  no_telepon VARCHAR(15),
  posyandu_name VARCHAR(200) NOT NULL,
  posyandu_address TEXT,
  kelurahan VARCHAR(100),
  kecamatan VARCHAR(100),
  kota VARCHAR(100),
  provinsi VARCHAR(100),
  document_url VARCHAR(500), -- URL surat tugas/SK
  rejection_reason TEXT,
  verified_by INT, -- ID super admin yang verifikasi
  verified_at TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id)
);
```

### 2.3 Tabel Anak (Existing - Update jika perlu)
```sql
CREATE TABLE anak (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nama VARCHAR(200) NOT NULL,
  nik_anak VARCHAR(16),
  tanggal_lahir DATE NOT NULL,
  jenis_kelamin ENUM('L', 'P') NOT NULL,
  nama_ortu VARCHAR(200),
  alamat TEXT,
  petugas_id INT NOT NULL, -- ID petugas yang input
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (petugas_id) REFERENCES users(id)
);
```

### 2.4 Tabel Pemeriksaan (Existing - Update jika perlu)
```sql
CREATE TABLE pemeriksaan (
  id INT PRIMARY KEY AUTO_INCREMENT,
  anak_id INT NOT NULL,
  tanggal_pemeriksaan DATE NOT NULL,
  berat_badan DECIMAL(5,2),
  tinggi_badan DECIMAL(5,2),
  lingkar_kepala DECIMAL(5,2),
  hasil_deteksi ENUM('normal', 'stunting', 'berisiko'),
  catatan TEXT,
  petugas_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (anak_id) REFERENCES anak(id) ON DELETE CASCADE,
  FOREIGN KEY (petugas_id) REFERENCES users(id)
);
```

---

## 3. FITUR LANDING PAGE (PUBLIC)

### 3.1 Halaman Utama
- **Route**: `/` atau `/home`
- **Komponen**:
  - Hero section dengan informasi stunting
  - Statistik stunting di Indonesia
  - Tentang AI Stunt Detect
  - FAQ
  - CTA untuk login petugas/registrasi

### 3.2 Halaman Informasi Stunting
- **Route**: `/tentang-stunting`
- **Konten**:
  - Apa itu stunting
  - Penyebab stunting
  - Pencegahan
  - Gejala dan dampak
  - Infografis

### 3.3 Halaman Layanan Posyandu
- **Route**: `/layanan`
- **Konten**:
  - Daftar layanan posyandu
  - Jadwal pemeriksaan
  - Peta posyandu terdekat (integrasi maps)

### 3.4 Halaman Registrasi Petugas
- **Route**: `/registrasi-petugas`
- **Form**:
  - Data pribadi (nama, NIK, email, telepon)
  - Data posyandu (nama, alamat lengkap)
  - Username & password
  - Upload dokumen (surat tugas/SK)
  - Checkbox persetujuan

### 3.5 Halaman Login
- **Route**: `/login`
- **Fungsi**:
  - Form login (username/email & password)
  - Redirect berdasarkan role:
    - Super Admin → `/super-admin/dashboard`
    - Petugas Posyandu → `/admin/dashboard`

---

## 4. FITUR SUPER ADMIN

### 4.1 Dashboard Super Admin
- **Route**: `/super-admin/dashboard`
- **Komponen**:
  - Statistik:
    - Total petugas terdaftar
    - Pending verifikasi
    - Total anak tercatat
    - Total pemeriksaan
  - Quick actions
  - Grafik pertumbuhan data

### 4.2 Manajemen Petugas Posyandu
- **Route**: `/super-admin/petugas`
- **Tab Navigation**:
  1. **Pending Verifikasi**
     - List petugas dengan status pending
     - Lihat detail profil
     - Lihat dokumen
     - Tombol Approve/Reject
     - Modal untuk alasan reject
  
  2. **Petugas Aktif**
     - List petugas terverifikasi
     - Search & filter (nama, posyandu, wilayah)
     - Aksi: Lihat detail, Edit, Suspend, Hapus
     - Export data
  
  3. **Riwayat Ditolak**
     - List petugas yang ditolak
     - Lihat alasan penolakan
     - Opsi untuk re-approve

### 4.3 Laporan Global
- **Route**: `/super-admin/laporan`
- **Fitur**:
  - Filter berdasarkan wilayah, periode
  - Laporan per posyandu
  - Export PDF/Excel
  - Grafik dan visualisasi data

---

## 5. FITUR PETUGAS POSYANDU (UPDATE)

### 5.1 Dashboard Petugas
- **Route**: `/admin/dashboard`
- **Komponen**:
  - Status akun (jika pending: tampilkan peringatan)
  - Statistik data anak di posyandu
  - Quick add data anak
  - Riwayat pemeriksaan terbaru

### 5.2 Manajemen Data Anak
- **Route**: `/admin/anak`
- **Fungsi**: (existing, tetap dipertahankan)
  - Tambah data anak
  - Edit data anak
  - Hapus data anak
  - Lihat detail anak & riwayat pemeriksaan

### 5.3 Input Pemeriksaan
- **Route**: `/admin/pemeriksaan`
- **Fungsi**: (existing, tetap dipertahankan)
  - Pilih anak
  - Input data pemeriksaan (BB, TB, LK)
  - AI deteksi stunting
  - Simpan hasil

---

## 6. ALUR KERJA SISTEM

### 6.1 Alur Registrasi Petugas Posyandu
```
1. Petugas buka landing page
2. Klik "Daftar Sebagai Petugas"
3. Isi form registrasi lengkap
4. Upload dokumen surat tugas/SK
5. Submit → Status: PENDING
6. Email notifikasi: "Registrasi berhasil, menunggu verifikasi"
7. Super Admin mendapat notifikasi ada registrasi baru
8. Super Admin review data & dokumen
9. Super Admin Approve/Reject:
   - APPROVE → Status: ACTIVE, kirim email: "Akun disetujui, silakan login"
   - REJECT → Status: REJECTED, kirim email: "Pendaftaran ditolak: [alasan]"
10. Petugas login (jika approved)
```

### 6.2 Alur Login
```
1. User buka /login
2. Input username/email & password
3. Validasi credentials
4. Cek role:
   - Super Admin → redirect ke /super-admin/dashboard
   - Petugas Posyandu:
     - Cek status:
       - ACTIVE → redirect ke /admin/dashboard
       - PENDING → tampilkan halaman "Menunggu Verifikasi"
       - REJECTED → tampilkan halaman "Pendaftaran Ditolak: [alasan]"
       - SUSPENDED → tampilkan halaman "Akun Disuspend"
```

### 6.3 Alur Pencatatan Data (Existing)
```
1. Petugas login (status ACTIVE)
2. Pilih menu "Tambah Anak Baru" atau "Input Pemeriksaan"
3. Isi data lengkap
4. Submit → Data tersimpan dengan petugas_id
5. Data bisa dilihat di dashboard petugas & super admin
```

---

## 7. TEKNOLOGI STACK

### 7.1 Frontend
- **Framework**: React.js / Next.js / Vue.js (pilih salah satu)
- **Styling**: Tailwind CSS / Bootstrap
- **State Management**: Redux / Context API
- **HTTP Client**: Axios

### 7.2 Backend
- **Framework**: 
  - PHP: Laravel / CodeIgniter
  - Node.js: Express.js
  - Python: Django / Flask
- **Authentication**: JWT / Session
- **File Upload**: Multer (Node) / Laravel Storage

### 7.3 Database
- **Database**: MySQL / PostgreSQL
- **ORM**: Eloquent (Laravel) / Sequelize (Node) / SQLAlchemy (Python)

### 7.4 AI Model
- **Model**: Existing AI model untuk deteksi stunting
- **Integration**: API endpoint untuk prediksi

---

## 8. IMPLEMENTASI BERTAHAP

### Phase 1: Database & Backend Setup (Week 1)
- [ ] Update database schema
- [ ] Buat migration files
- [ ] Seed data super admin pertama
- [ ] Setup authentication & role middleware
- [ ] Buat API endpoints untuk registrasi
- [ ] Buat API endpoints untuk verifikasi petugas
- [ ] Buat API endpoints untuk manajemen petugas
- [ ] Setup file upload untuk dokumen

### Phase 2: Landing Page (Week 2)
- [ ] Design mockup landing page
- [ ] Implementasi halaman home
- [ ] Implementasi halaman tentang stunting
- [ ] Implementasi halaman layanan
- [ ] Implementasi halaman registrasi petugas
- [ ] Implementasi halaman login
- [ ] Responsive design untuk mobile

### Phase 3: Super Admin Dashboard (Week 3)
- [ ] Design mockup dashboard super admin
- [ ] Implementasi dashboard utama
- [ ] Implementasi halaman manajemen petugas
- [ ] Implementasi fitur approve/reject
- [ ] Implementasi fitur edit/suspend/hapus petugas
- [ ] Implementasi halaman laporan global
- [ ] Notifikasi untuk registrasi baru

### Phase 4: Update Dashboard Petugas (Week 4)
- [ ] Update UI dashboard petugas
- [ ] Tambahkan status verifikasi di dashboard
- [ ] Implementasi halaman "Menunggu Verifikasi"
- [ ] Implementasi halaman "Pendaftaran Ditolak"
- [ ] Update fitur pencatatan (pastikan petugas_id tersimpan)
- [ ] Testing integrasi dengan existing features

### Phase 5: Testing & Deployment (Week 5)
- [ ] Unit testing semua fitur
- [ ] Integration testing
- [ ] User acceptance testing (UAT)
- [ ] Bug fixing
- [ ] Security audit
- [ ] Deployment ke server
- [ ] Dokumentasi pengguna

---

## 9. API ENDPOINTS

### 9.1 Public Endpoints
```
POST   /api/register-petugas     - Registrasi petugas baru
POST   /api/login                - Login semua user
GET    /api/public/info-stunting - Info stunting untuk landing page
GET    /api/public/layanan       - Info layanan posyandu
```

### 9.2 Super Admin Endpoints
```
GET    /api/super-admin/dashboard/stats      - Statistik dashboard
GET    /api/super-admin/petugas              - List semua petugas
GET    /api/super-admin/petugas/pending      - List petugas pending
GET    /api/super-admin/petugas/:id          - Detail petugas
POST   /api/super-admin/petugas/:id/approve  - Approve petugas
POST   /api/super-admin/petugas/:id/reject   - Reject petugas
PUT    /api/super-admin/petugas/:id          - Edit data petugas
DELETE /api/super-admin/petugas/:id          - Hapus petugas
POST   /api/super-admin/petugas/:id/suspend  - Suspend petugas
GET    /api/super-admin/laporan              - Laporan global
```

### 9.3 Petugas Posyandu Endpoints
```
GET    /api/petugas/dashboard/stats  - Statistik dashboard petugas
GET    /api/petugas/profile          - Profil petugas
PUT    /api/petugas/profile          - Update profil
GET    /api/petugas/anak             - List anak (existing)
POST   /api/petugas/anak             - Tambah anak (existing)
PUT    /api/petugas/anak/:id         - Edit anak (existing)
DELETE /api/petugas/anak/:id         - Hapus anak (existing)
GET    /api/petugas/pemeriksaan      - List pemeriksaan (existing)
POST   /api/petugas/pemeriksaan      - Tambah pemeriksaan (existing)
```

---

## 10. KEAMANAN

### 10.1 Authentication & Authorization
- [ ] JWT token dengan expiry time
- [ ] Refresh token mechanism
- [ ] Role-based access control (RBAC)
- [ ] Middleware untuk cek role di setiap endpoint
- [ ] Password hashing (bcrypt/argon2)

### 10.2 Data Validation
- [ ] Input validation di frontend & backend
- [ ] Sanitize input untuk mencegah XSS
- [ ] Prepared statements untuk mencegah SQL injection
- [ ] File upload validation (type, size)
- [ ] Rate limiting untuk API

### 10.3 Privacy & Data Protection
- [ ] Enkripsi data sensitif (NIK)
- [ ] HTTPS untuk semua komunikasi
- [ ] Audit log untuk aksi super admin
- [ ] Backup database berkala

---

## 11. NOTIFIKASI

### 11.1 Email Notifications
- Registrasi berhasil (ke petugas)
- Pendaftaran baru (ke super admin)
- Persetujuan akun (ke petugas)
- Penolakan akun dengan alasan (ke petugas)
- Suspend akun (ke petugas)

### 11.2 In-App Notifications
- Badge notifikasi untuk super admin (pending registrations)
- Alert untuk petugas jika status berubah

---

## 12. DOKUMENTASI

### 12.1 Dokumentasi Teknis
- [ ] API documentation (Swagger/Postman)
- [ ] Database schema diagram
- [ ] System architecture diagram
- [ ] Setup & deployment guide

### 12.2 User Guide
- [ ] Panduan untuk masyarakat umum
- [ ] Panduan registrasi petugas posyandu
- [ ] Panduan penggunaan untuk petugas
- [ ] Panduan untuk super admin
- [ ] FAQ

---

## 13. TESTING CHECKLIST

### 13.1 Functional Testing
- [ ] Registrasi petugas berhasil
- [ ] Upload dokumen berhasil
- [ ] Super admin dapat approve/reject
- [ ] Email notifikasi terkirim
- [ ] Login berdasarkan role berhasil
- [ ] Petugas pending tidak bisa akses dashboard
- [ ] Petugas active bisa melakukan pencatatan
- [ ] Data tersimpan dengan petugas_id yang benar

### 13.2 Security Testing
- [ ] SQL injection test
- [ ] XSS test
- [ ] CSRF protection
- [ ] Unauthorized access test
- [ ] File upload exploit test

### 13.3 Performance Testing
- [ ] Load testing untuk concurrent users
- [ ] Database query optimization
- [ ] Image/file upload performance

---

## 14. MONITORING & MAINTENANCE

### 14.1 Monitoring
- Server uptime monitoring
- Error logging & tracking
- User activity analytics
- Database performance monitoring

### 14.2 Maintenance Plan
- Regular backup schedule
- Security updates
- Bug fixes & feature improvements
- User feedback collection

---

## 15. FUTURE ENHANCEMENTS (Optional)

- [ ] Mobile app untuk petugas posyandu
- [ ] Notifikasi push
- [ ] Chat support
- [ ] Integrasi dengan sistem pemerintah
- [ ] Dashboard analytics lebih advanced
- [ ] Export data ke format standard (PDF, Excel)
- [ ] Multi-language support
- [ ] Kalkulator pertumbuhan anak di landing page
- [ ] Peta interaktif posyandu terdekat
- [ ] Appointment booking untuk pemeriksaan

---

## NOTES

- Pastikan existing features (pencatatan anak & pemeriksaan) tetap berfungsi
- Lakukan backup database sebelum migrasi
- Test secara menyeluruh di development environment
- Dokumentasikan semua perubahan kode
- Gunakan version control (Git) untuk tracking changes

---

**Created**: 2025-04-01
**Last Updated**: 2025-04-01
**Status**: Ready for Implementation
