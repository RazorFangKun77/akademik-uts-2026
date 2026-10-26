# Rekap Nilai Mahasiswa UTS

Proyek ini dibuat untuk menampilkan data nilai mahasiswa dengan fitur pencarian, pengurutan, dan paginasi. Selain itu, setiap data mahasiswa juga menampung URL foto profil agar dapat ditampilkan di kolom Nama Mahasiswa.

## Fitur utama
- Menyimpan foto mahasiswa pada kolom `file_path` di tabel `nilai`
- Menampilkan foto kecil di sebelah nama mahasiswa pada kolom Nama Mahasiswa
- Pencarian berdasarkan nama mahasiswa
- Filter berdasarkan predikat
- Pengurutan data berdasarkan Nilai Akhir dengan ikon panah
- Paginasi dengan opsi 5, 10, dan 15 record per halaman
- Data dummy untuk demonstrasi fitur

## Struktur database
File SQL dump yang berisi struktur dan data dummy disimpan di:
- `akademik_db.sql`

## Cara menjalankan
1. Pastikan XAMPP MySQL aktif.
2. Import file `akademik_db.sql` ke database MySQL lokal.
3. Buka `viewdata.php` di browser.

## Catatan penting
- Aplikasi menggunakan database `akademik_db`.
- Kolom `file_path` dapat diisi dengan URL foto (contoh: `https://...jpg`).
- Jika kolom `file_path` kosong, sistem akan menampilkan avatar default otomatis.
