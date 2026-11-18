# AQUANEST – Ensiklopedia Biota Laut Nusantara (PHP Native + MySQL)

Repositori ini berisi implementasi lengkap **HTML, CSS, JavaScript, dan PHP Native (PDO + Session + MySQL)** siap pakai untuk tugas responsi praktikum pemrograman web, dapat dijalankan di **XAMPP** atau **Laragon**.

## Struktur Direktori
- `sql/aquanest_db.sql` — Skema 9 tabel relasional (`users`, `categories`, `habitats`, `conservation_status`, `species`, `educational_materials`, `quizzes`, `quiz_questions`, `quiz_results`) beserta 30 data spesies biota laut Indonesia.
- `config/database.php` — Konfigurasi koneksi PDO MySQL (`aquanest_db`).
- `includes/auth.php` — Manajemen sesi login, role `user`/`admin`, dan perlindungan CSRF.
- `index.php`, `species.php`, `species-detail.php`, `ecosystems.php`, `education.php`, `quiz.php` — Halaman publik & kuis interaktif.
- `login.php`, `register.php`, `logout.php` — Autentikasi pengguna.
- `admin/dashboard.php` — Dashboard admin dinamis dengan CRUD spesies, materi edukasi, kuis, dan upload foto nyata.
- `assets/images/` — 38 foto asli satwa liar & ekosistem bawah laut Nusantara.

## Cara Instalasi di XAMPP / Laragon
1. Salin seluruh folder `php-aquanest` ke `C:\xampp\htdocs\aquanest` (XAMPP) atau `C:\laragon\www\aquanest` (Laragon).
2. Jalankan layanan **Apache** dan **MySQL**.
3. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`), buat database baru bernama `aquanest_db` (atau langsung jalankan tab **Import** dan pilih `sql/aquanest_db.sql`).
4. Sesuaikan kredensial database pada `config/database.php` jika diperlukan (default: user `root`, password kosong `""`).
5. Buka browser ke `http://localhost/aquanest/` (atau `http://aquanest.test/` di Laragon).

## Akun Pengujian Default
- **Admin Kurator**: `admin@aquanest.id` / `AdminAquanest2026!`
- **Pengguna Biasa**: `penjelajah@aquanest.id` / `LautNusantara2026!`

## Tampilan & Tips Konservasi
- Tata letak: hero besar, tentang, spesies pilihan (carousel), tips konservasi, kutipan, footer. Palet hijau/cokelat dengan sentuhan biru langit; semua warna ada di `:root` pada `assets/css/style.css`.
- Tabel baru **`conservation_tips`** (judul, kategori, ikon, tingkat kesulitan, ringkasan, penjelasan, relasi opsional ke `species` dan `habitats`, urutan, status aktif). Sudah termasuk di `sql/aquanest_db.sql` beserta 12 tips awal.
- Sudah punya database lama? Import saja `sql/conservation_tips.sql` (hanya menambah tabel tips; tabel lain tidak disentuh).
- Halaman `tips.php` (filter kategori), tips terkait di halaman detail spesies, dan kelola tips (tambah, aktif/nonaktif, hapus) di Dashboard Admin.
- Ikon tips dan helper tampilan ada di `includes/ui.php`; JavaScript dasar (menu mobile, carousel) di `assets/js/app.js`.
