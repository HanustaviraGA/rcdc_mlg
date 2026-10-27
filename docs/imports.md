# Pengaturan force import

Atur masing-masing fitur melalui `.env`, lalu jalankan `php artisan config:clear`.
Nilai default semua pengaturan adalah `false` (validasi normal).

| Variabel | Fitur |
| --- | --- |
| `IMPORT_RECTORATE_FM_FORCE` | Publikasi FM, termasuk sheet KPI |
| `IMPORT_RECTORATE_MHS_FORCE` | Publikasi mahasiswa |
| `IMPORT_RECTORATE_HIBAH_FORCE` | Hibah penelitian |
| `IMPORT_DOSEN_DATA_CENTER_FORCE` | Data Center dosen |
| `IMPORT_DOSEN_STATUS_PENDIDIKAN_FORCE` | Status Pendidikan dosen |
| `IMPORT_PKM_FORCE` | Community Development (Rasio dan Keterlibatan), serta template PKM lama List/CE |

Gunakan `true` untuk mengaktifkan force pada fitur yang dipilih. Flag fitur lain
tetap terpisah. Flag hanya dibaca dari konfigurasi server, bukan parameter upload.

Kolom wajib tetap harus dipetakan ke indeks kolom yang valid dan berbeda.
Daftar kolom FM/MHS/Hibah tetap mengikuti `ColumnMapperService`. Data Center dan
Status Pendidikan memakai pemetaan masing-masing. Community Development memakai
konfigurasi `pkm_ratio` dan `pkm_involvement` pada `ColumnMapperService`.

Saat force aktif, sel wajib yang kosong tetap diimpor jika dapat disimpan; angka
tidak valid dikosongkan. Baris tanpa identitas yang diperlukan, tahun anggaran
hibah tidak valid, dan duplikat dengan isi berbeda dilewati. Untuk duplikat,
data pertama dipakai. LIST/TITLE mahasiswa yang tidak valid memakai rekap dari
MALANG/Raw; sheet KPI FM dengan header tidak valid dilewati. Hasil menyertakan
`force_import`, `skipped_rows`, serta `warnings`, yang juga tampil pada ringkasan.

File/sheet tidak terbaca, pemetaan salah, periode salah, prasyarat Data Center
untuk status pendidikan, migrasi belum tersedia, dan kegagalan database tetap
menghentikan impor. Tidak ada penggantian data jika semua baris tidak dapat
diimpor. Perubahan database tetap dalam transaksi agar kegagalan penyimpanan
mengembalikan data sebelumnya. Filter kampus dan kategori publikasi FM/MHS tetap
berlaku. Hibah tidak difilter berdasarkan lokasi; Lokasi Kampus mengikuti kolom
yang dikonfirmasi pada preview pemetaan.

## Import Community Development

Jalankan migrasi `2026_10_23_000000_create_community_development_imports.php` sebelum
mengimpor melalui Modules/ImportPKM. Pilih tahun akademik berurutan dan semester,
misalnya `2025/2026 Ganjil` atau `2025/2026 Genap`, lalu konfirmasikan pemetaan
sheet Rasio dan Keterlibatan. Unggah ulang mengganti kedua sheet pada periode
yang sama dalam satu transaksi; periode lain tetap tersimpan. Riwayat menyimpan
nama file, pemetaan, baris header, serta waktu import terakhir.

Kontrak internal (autentikasi sesi dan CSRF seperti import dosen):

- `POST /backoffice/importpkm/preview`: multipart `pkm` (.xlsx, maks. 20 MB).
  Respons `success`, `sheets.rasio` dan `sheets.keterlibatan`, masing-masing berisi
  `header_row`, `headers`, `preview_data`, `suggested_mapping`, `mapping_config`.
- `POST /backoffice/importpkm/upload`: multipart `pkm`, `academic_year`
  (`2025/2026`), `period` (`Ganjil`/`Genap`), `column_mapping` (JSON dengan kunci
  `rasio` dan `keterlibatan`, indeks kolom mulai nol), `header_rows` (JSON berisi
  nomor baris header masing-masing sheet, mulai satu). Respons memuat `summary`,
  `warnings`, `message`, dan `history_html`. Pemetaan/periode/file tidak valid
  mengembalikan 422; pengguna belum masuk mendapat 401 untuk permintaan JSON.

Sheet Rasio menjadi sumber rekap prodi dan kartu: jumlah FM, kegiatan, rasio,
termasuk baris total jika ada. Tanpa baris total, jumlah dihitung dari rincian.
Rasio kosong dihitung dari kegiatan dibagi FM bila keduanya tersedia.
Sheet Keterlibatan menyimpan kode dosen, **jumlah kegiatan yang dilaporkan ke
Binusmaya (kolom H pada template)**, skor KPI, dan keterangan. Kolom G tidak
digunakan untuk jumlah kegiatan pada rincian dosen. Pemetaan yang dikonfirmasi
tetap berlaku apabila urutan kolom berubah.

Nama dan prodi dibaca dari `database_dosen_new` milik Modules/Dosen saat dashboard
dibuka; identitas pada workbook PKM tidak menimpa profil dosen. English Literature
dan Chinese Literature dikelompokkan ke DLLC. Kode yang belum cocok dilaporkan,
tetap disimpan, dan otomatis terhubung ketika data dosen tersedia. Dosen yang
belum memiliki prodi dalam rekap dapat dilihat melalui daftar terpisah.

`/kpi-publikasi` memakai periode akademik terbaru secara default, atau parameter
`community_period` (ID periode import) untuk memilih periode sebelumnya. Filter
publikasi tetap terpisah. Data mitra, kluster, skala, dan area masih contoh dan
diberi label tersendiri. Sebelum import pertama, pratinjau demo tetap tersedia.
Endpoint/template List/CE lama tetap dipertahankan untuk kompatibilitas.
