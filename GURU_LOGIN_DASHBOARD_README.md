# Sistem Login dan Dashboard Guru - Pemilihan Ketua OSIS

## 📋 Fitur Utama

### 1. Login Page
- URL: `/guru/login`
- Login menggunakan **username** dan **password**
- Design modern dengan gradient background
- Form validation yang jelas

### 2. Dashboard Guru
- URL: `/guru/dashboard` (requires authentication)
- Menampilkan statistik lengkap tahun ajaran **2025/2026**:
  - **Total Siswa**: Jumlah semua siswa tahun 2025/2026
  - **Siswa Putra**: Jumlah siswa laki-laki berdasarkan `biodatas.jenis_kelamin = 'L'`
  - **Siswa Putri**: Jumlah siswa perempuan berdasarkan `biodatas.jenis_kelamin = 'P'`
  - **Siswa Sudah Voting**: Jumlah siswa yang telah memberikan suara
  - **Siswa Belum Voting**: Jumlah siswa yang belum voting
  - **Progress Voting**: Persentase siswa yang sudah voting

### 3. Halaman Statistik Voting
- URL: `/guru/statistics` (requires authentication)
- Menampilkan detail perolehan suara per calon:
  - **Calon Putra**: Card dengan foto, jumlah suara, dan progress bar
  - **Calon Putri**: Card dengan foto, jumlah suara, dan progress bar
  - Total suara per kategori (putra/putri)
  - Persentase suara dari masing-masing kategori

## 🔐 Authentication

### Login Credentials

Tersedia beberapa opsi login:

#### Test Accounts (dari GuruSeeder):
```
Username: admin
Password: password
---
Username: guru1
Password: password
---
Username: guru2
Password: password
```

#### Guru Existing (dari database):
```
Username: cahya (Cahya Widi Rahayu)
Username: hepy (Hepy Puji Setiawan, S.Pd. Gr)
Username: adieb (Adieb Ajie Bayu Mukti, S. Pd)
Username: nuzul (Assabun Nuzul S.Kom)
... dan banyak lagi
```

Untuk guru existing, password default tergantung dari aplikasi sebelumnya. Jika menggunakan GuruSeeder, semua password adalah `password`.

### Session Management
- Menggunakan Laravel Session-based authentication
- Remember token untuk "Remember Me" feature
- CSRF protection pada semua form
- Logout button pada setiap halaman

## 📊 Data Statistik

### Perhitungan:

#### Siswa Putra/Putri
```php
// Query dengan eager loading dari biodata
Siswa::whereHas('biodata', function ($query) {
    $query->where('jenis_kelamin', 'L'); // atau 'P'
})->where('tahun', '2025/2026')->count();
```

#### Siswa Voting Status
```php
// Sudah voting
Vote::distinct('nis')
    ->whereIn('nis', Siswa::where('tahun', '2025/2026')->pluck('nis'))
    ->count();

// Belum voting
$siswaTotal - $siswaSudahVoting
```

#### Perolehan Suara Per Calon
```php
// Count votes dengan relationship
Calon::withCount('votes')
    ->where('jenis_kelamin', 'putra')
    ->orderBy('nomor_urut')
    ->get();
```

## 🎨 UI/UX Features

### Dashboard
- Card-based layout dengan shadow effects
- Color-coded statistics:
  - 🔵 Blue: Total dan Siswa Putra
  - 💗 Pink: Siswa Putri
  - 🟢 Green: Progress/Status voting
- Percentage display untuk demographic breakdown
- Button "Lihat Detail Hasil Voting" ke statistics page

### Statistics Page
- Grid layout responsive (1 column mobile, 3 columns desktop)
- Photo di atas (h-64) untuk visibility lebih baik
- Vote count dengan progress bar visual
- Persentase dari total suara kategori
- Summary section di bawah dengan total semua suara

## 🔗 Routes

| Method | Route | Name | Middleware | Description |
|--------|-------|------|------------|-------------|
| GET | `/guru/login` | `guru.login` | guest | Show login form |
| POST | `/guru/login` | `guru.login.post` | guest | Process login |
| POST | `/guru/logout` | `guru.logout` | auth | Logout |
| GET | `/guru/dashboard` | `guru.dashboard` | auth | Dashboard statistik |
| GET | `/guru/statistics` | `guru.statistics` | auth | Detail voting statistics |

## 📁 File Structure

```
app/Http/Controllers/
├── GuruAuthController.php        # Login/Logout logic
└── GuruDashboardController.php   # Dashboard & Statistics

resources/views/guru/
├── login.blade.php              # Login form
├── dashboard.blade.php          # Dashboard with stats
└── statistics.blade.php         # Voting results

database/seeders/
└── GuruSeeder.php              # Create test guru accounts

routes/
└── web.php                       # Guru routes
```

## 🚀 Cara Menggunakan

### 1. Login
```
URL: http://pilketos.test/guru/login
Username: admin
Password: password
```

### 2. Akses Dashboard
Setelah login berhasil, secara otomatis redirect ke:
```
http://pilketos.test/guru/dashboard
```

### 3. Lihat Statistik Detail
Click button "Lihat Detail Hasil Voting" di dashboard, atau akses langsung:
```
http://pilketos.test/guru/statistics
```

### 4. Logout
Click tombol "Logout" di navbar

## 📈 Data Flow

```
Login Page (/guru/login)
    ↓
GuruAuthController::login()
    ↓ [Verify credentials]
    ├─ Success → Session regenerate → Redirect to Dashboard
    └─ Failed → Redirect back with error
    
Dashboard (/guru/dashboard)
    ├─ Query siswa tahun 2025/2026
    ├─ Count by jenis_kelamin (L/P)
    ├─ Count voting status
    └─ Display statistics cards
    
Statistics (/guru/statistics)
    ├─ Query calons with vote count
    ├─ Calculate percentages
    └─ Display per-calon statistics
```

## 🔒 Security Features

✅ **Authentication Required**: Dashboard dan Statistics hanya bisa diakses setelah login
✅ **CSRF Protection**: Semua form dilindungi CSRF token
✅ **Session Management**: Proper session regeneration saat login
✅ **Password Hashing**: Semua password di-hash dengan bcrypt
✅ **Guest Middleware**: Login form hanya bisa diakses jika belum login
✅ **Auth Middleware**: Dashboard hanya bisa diakses jika sudah login

## 📝 Notes

- Tahun ajaran hard-coded ke '2025/2026' di controller
- Jenis kelamin: 'L' = Putra (Laki-laki), 'P' = Putri
- Vote count menggunakan `withCount('votes')` untuk efficient query
- Progress percentage calculated di blade untuk real-time accuracy
- Responsive design: mobile-first approach dengan Tailwind CSS

