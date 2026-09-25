# Dosen: arsip, visibilitas, dan proyek riset

- Tombol delete pada Modules/Dosen mengisi `database_dosen_new.deleted_at` (soft delete). Dosen keluar dari daftar aktif; identitas, foto, atribut, serta riwayat publikasi dan riset tetap tersimpan.
- Kolom **Sembunyikan profil** di kanan Kode Dosen menyimpan `is_hidden`. Jika dicentang, profil tidak muncul pada beranda/daftar dosen dan URL detail mengembalikan 404. Dosen tetap tersedia di admin; hapus centang untuk menampilkan profil kembali. Pengaturan ini berlaku pada profil dosen, bukan penghapusan kontribusi historis di KPI atau Research Gallery.
- Impor ulang master Excel memperbarui kolom yang disertakan tanpa mengubah status tersembunyi atau mengaktifkan kembali dosen yang sudah dihapus.
- Detail dosen memakai katalog yang sama dengan Research Gallery: `researchs` (Sistem Riset) dan snapshot hibah aktif dari Upload Rectorate. Pencocokan menggunakan kode dosen pada seluruh anggota proyek, termasuk peneliti pendamping.
- Tiga proyek terbaru ditampilkan berdasarkan tahun anggaran, lalu judul. Judul dan **Baca detail** membuka detail Research Gallery. **Lihat Lainnya** di bawah daftar membuka galeri dengan `source=all`, `person=<kode_dosen>`, dan `sort=new`.
- Proyek dengan identitas berbeda pada kedua sumber tetap menjadi entri terpisah, mengikuti perilaku Research Gallery. Halaman tanpa proyek menampilkan keterangan kosong.

Jalankan migrasi `2026_09_25_000000_add_visibility_and_soft_deletes_to_database_dosen_new.php` sebelum menggunakan perubahan ini. Endpoint delete dan visibilitas memerlukan sesi login.
