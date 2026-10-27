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
| `IMPORT_PKM_FORCE` | Community Development (Rasio, Keterlibatan, mitra UMKM), serta template PKM lama List/CE |

Gunakan `true` untuk mengaktifkan force pada fitur yang dipilih. Flag fitur lain
tetap terpisah. Flag hanya dibaca dari konfigurasi server, bukan parameter upload.

Kolom wajib tetap harus dipetakan ke indeks kolom yang valid dan berbeda.
Daftar kolom FM/MHS/Hibah tetap mengikuti `ColumnMapperService`. Data Center dan
Status Pendidikan memakai pemetaan masing-masing. Community Development memakai
konfigurasi `pkm_ratio`, `pkm_involvement`, dan `pkm_partners` pada `ColumnMapperService`.

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

`/dashboard-comdev` memakai periode akademik terbaru secara default, atau parameter
`community_period` (ID periode import) untuk memilih periode sebelumnya. Halaman ini
terpisah dari `/kpi-publikasi` dan tersedia melalui navigasi publik. Sebelum import
PKM pertama, rekap dan rincian dosen masih diberi label data contoh.
Endpoint/template List/CE lama tetap dipertahankan untuk kompatibilitas.

## Import mitra binaan / UMKM

Jalankan migration `2026_10_27_000000_create_community_partner_imports.php` untuk
membuat `community_partner_imports` (tahun rekap unik, nama file, sheet, baris header,
pemetaan, jumlah mitra, waktu impor) dan `community_partners` (nama usaha/mitra,
kluster, skala, area, tahun bergabung, baris sumber). Foreign key menghapus rincian
ketika riwayat terkait dihapus. Migration PKM Rasio/Keterlibatan tetap memakai
`2026_10_23_000000_create_community_development_imports.php`.

Pada Modules/ImportPKM tersedia formulir **Import mitra binaan / UMKM** tersendiri.
Pilih tahun data dan file `.xlsx` atau `.xlsb` (maksimal 20 MB), kemudian pilih
sheet dan baris header jika deteksi awal belum sesuai. Petakan **Nama Usaha,
Cluster, Skala, Area**, serta **Tahun Bergabung** bila tersedia; konfirmasikan
pemetaan sebelum mengimpor. Indeks pemetaan mulai nol. Pemetaan ganda, indeks
di luar kolom, dan kolom wajib yang tidak dipetakan ditolak. Kolom opsional yang
tidak dipetakan tetap kosong. Kolom pemilik, KTP, kontak dan alamat tidak disimpan.

Nama, kluster dan area wajib terisi. Skala kosong untuk kluster NON UMKM menjadi
`Lainnya`; skala kosong pada mitra lain menjadi `Belum diketahui`. Penulisan umum
Kabupaten/Kab./Kab Malang diseragamkan menjadi Kab. Malang. Area lain tetap tersimpan
pada rincian, tetapi digabung ke Lainnya pada grafik. Kluster dan skala dihitung dari
kategori yang tersedia di data. Duplikat dengan semua kolom terpetakan identik
dilewati; nama sama dengan area berbeda tetap tersimpan. Tahun bergabung opsional
harus berupa tahun 1900–2100. Force import mengikuti `IMPORT_PKM_FORCE` dan
melaporkan baris yang dilewati/nilai yang dikosongkan.

Unggah ulang mengganti seluruh mitra pada **tahun data yang sama** dalam satu
transaksi. Tahun lain dan rekap PKM dosen tetap tersimpan. Tahun data merupakan
tahun snapshot, bukan tahun bergabung atau tahun akademik. Validasi diselesaikan
sebelum penggantian; kegagalan database membatalkan transaksi. Impor tanpa baris
valid ditolak, termasuk ketika force aktif.

Kontrak internal formulir (autentikasi sesi `auth`, mengirim header X-CSRF-TOKEN
mengikuti formulir ImportPKM):

- `POST /backoffice/importpkm/partners/preview`: multipart `partners`, opsional
  `sheet` dan `header_row` (1–1000). Respons `success`, `sheet`, `sheets`,
  `header_row`, `headers`, `preview_data` (maks. 5 baris), `mapping_config`,
  `suggested_mapping`. Sheet kosong tetap mengembalikan daftar sheet agar pengguna
  dapat memilih sheet lain. Deteksi header memeriksa 50 baris awal yang terisi.
- `POST /backoffice/importpkm/partners/upload`: multipart `partners`, `data_year`
  (2000–2100), `sheet`, `header_row`, `column_mapping` (objek JSON).
  Respons `success`, `summary` (`import_id`, `data_year`, `selected`, `duplicates`,
  `replaced`, `force_import`, `skipped_rows`, `warnings`), `message`, `warnings`,
  `history_html`. Kesalahan input mengembalikan 422 dengan `errors`; permintaan
  JSON tanpa login mengembalikan 401. Kegagalan database mengembalikan respons
  kesalahan server tanpa mengganti snapshot sebelumnya.

Pembacaan XLSB berjalan di PHP melalui BIFF12, tanpa konversi manual, Excel,
Python, atau Node di server. Hanya nilai sel dan hasil formula tersimpan yang
dibaca; formula tidak dihitung ulang. Batas isi arsip 80 MB, 100.000 baris terisi,
dan 256 kolom per sheet berlaku untuk kedua format.

Dashboard memilih tahun mitra terbaru, atau `partner_year=2025` untuk tahun lain.
Pilihan tahun mitra dan `community_period` saling dipertahankan pada kedua formulir
filter. Sebelum import mitra pertama, jumlah dan grafik bernilai nol dengan pesan
belum ada data; tidak menggunakan mitra contoh. Rincian kluster/skala mendukung
pencarian nama, area, dan tahun bergabung.
