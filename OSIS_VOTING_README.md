# Aplikasi Pemilihan Ketua OSIS

Aplikasi web modern untuk pemilihan Ketua OSIS yang dibangun menggunakan Laravel 12 dengan Livewire 3 dan Tailwind CSS v4.

## Fitur Utama

### 📋 Landing Page
- Tampilan flyer calon Ketua OSIS Putra (3 calon)
- Tampilan flyer calon Ketua OSIS Putri (3 calon)
- Call-to-action untuk mulai voting
- Desain responsif dan menarik dengan gradien indigo-purple

### 🗳️ Sistem Voting
1. **Verifikasi NIS**: Siswa memasukkan NIS mereka untuk diverifikasi
2. **Tampil Data Siswa**: Sistem menampilkan nama dan kelas siswa jika NIS valid
3. **Pemilihan**: Siswa dapat memilih satu calon dari kategori putra atau putri
4. **Keamanan**: Setiap NIS hanya bisa voting 1 kali dan tidak dapat diubah setelah voting

### 🔄 Data Siswa
- Sistem otomatis mengambil data siswa **terbaru** (berdasarkan `created_at`) jika ada duplikat NIS
- Relasi menggunakan NIS, bukan User ID
- Dukungan untuk siswa kelas 7-9

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend UI**: Livewire 3 + Volt, Tailwind CSS v4
- **Database**: MariaDB
- **Testing**: Pest PHP 4
- **Code Quality**: Laravel Pint

## Instalasi & Cara Kerja

### 1. Jalankan Migrations
```bash
php artisan migrate
```

### 2. Seed Data Calon OSIS (Opsional)
```bash
php artisan db:seed --class=CalonSeeder
```

Ini akan menambahkan 6 calon (3 putra, 3 putri) ke database dengan data contoh.

### 3. Akses Aplikasi

#### Landing Page
```
http://localhost:8000/osis
```

#### Voting Page
```
http://localhost:8000/osis/voting
```

### 4. Jalankan Tests
```bash
php artisan test --compact tests/Feature/OsisVotingTest.php
```

## Struktur Database

### Tabel `calons`
```sql
- id (Primary Key)
- nama (string)
- jenis_kelamin (enum: 'putra', 'putri')
- nomor_urut (integer)
- foto_url (text, nullable)
- deskripsi (text, nullable)
- timestamps
```

### Tabel `votes`
```sql
- id (Primary Key)
- nis (string, unique) - NIS siswa
- calon_id (foreign key ke calons)
- timestamps
```

### Tabel `siswas`
```sql
- id (Primary Key)
- nis (string, nullable)
- kelas_id (foreign key)
- tahun (string)
- tingkat (integer: 7-9)
- pindahan (boolean)
- timestamps
```

## Model & Relasi

### Model `Calon`
```php
- votes(): HasMany
- countVotes(): int
```

### Model `Vote`
```php
- calon(): BelongsTo
```

### Model `Siswa`
```php
- kelas(): BelongsTo
- user(): BelongsTo (menggunakan NIS)
- vote(): HasOne (menggunakan NIS)
```

## Livewire Component

### `VotingInterface`
Component utama untuk voting interface yang menangani:
- Input dan validasi NIS
- Verifikasi data siswa
- Pengecekan voting duplikat
- Pemilihan calon
- Penyimpanan vote ke database

**Public Properties:**
- `nis` - NIS yang diinput
- `siswa` - Data siswa yang terverifikasi
- `siswaVerified` - Status verifikasi
- `errorMessage` - Pesan error (jika ada)
- `calonsPutra` - List calon putra
- `calonsPutri` - List calon putri
- `selectedCalonId` - ID calon yang dipilih
- `hasVoted` - Status apakah sudah voting

**Methods:**
- `checkNis()` - Verifikasi NIS
- `resetForm()` - Reset form
- `selectCalon($calonId)` - Pilih calon
- `submitVote()` - Submit vote

## Keamanan

✅ **Validasi di Level Database**:
- Constraint `UNIQUE` pada kolom `nis` di tabel `votes` mencegah double voting

✅ **Validasi di Level Aplikasi**:
- Check NIS sebelum voting
- Penolakan jika sudah voting sebelumnya
- Error message yang jelas untuk user

✅ **Data Siswa Terbaru**:
- Query menggunakan `latest('created_at')` untuk mengambil data siswa paling baru
- Penting untuk siswa yang naik kelas

## Customization

### Menambah Calon OSIS

#### Via Seeder (Recommended)
Edit `database/seeders/CalonSeeder.php` dan tambahkan:
```php
Calon::create([
    'nama' => 'Nama Calon',
    'jenis_kelamin' => 'putra', // atau 'putri'
    'nomor_urut' => 1,
    'deskripsi' => 'Deskripsi calon...',
    'foto_url' => null, // atau URL foto
]);
```

Kemudian jalankan:
```bash
php artisan db:seed --class=CalonSeeder
```

#### Via Tinker
```bash
php artisan tinker
> Calon::create(['nama' => '...', 'jenis_kelamin' => 'putra', 'nomor_urut' => 1])
```

### Mengubah Desain

Semua styling menggunakan **Tailwind CSS v4**. Edit:
- `resources/views/osis/landing.blade.php` - Landing page
- `resources/views/livewire/voting-interface.blade.php` - Voting interface

## Troubleshooting

### Routes tidak ditemukan
```bash
php artisan route:clear
php artisan route:cache
```

### Database sync error
```bash
php artisan migrate:fresh --seed
```

### Frontend tidak terupdate
```bash
npm run dev
# atau untuk production
npm run build
```

## File-File Penting

```
app/
├── Http/Controllers/OsisController.php
├── Livewire/VotingInterface.php
├── Models/
│   ├── Calon.php
│   ├── Vote.php
│   └── Siswa.php

database/
├── migrations/
│   ├── 2026_01_28_133555_create_calons_table.php
│   ├── 2026_01_28_133555_create_votes_table.php
│   └── ...
├── factories/
│   ├── CalonFactory.php
│   └── SiswaFactory.php
└── seeders/
    └── CalonSeeder.php

resources/views/osis/
├── landing.blade.php
└── voting.blade.php

tests/Feature/
└── OsisVotingTest.php
```

## Support

Untuk pertanyaan atau issues, silakan hubungi tim development.

---

**Created with ❤️ using Laravel 12 & Livewire 3**
