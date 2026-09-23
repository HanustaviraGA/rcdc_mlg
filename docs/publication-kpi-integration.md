# Integrasi dashboard KPI publikasi FM

## Tampilan aktif — kembali ke contoh HTML, 23 September 2026

Sesuai koreksi pengguna, tampilan `/kpi-publikasi` sekarang mengikuti komposisi `Dashboard_KPI_Publikasi_FM_2027.html`: sepuluh bagian berurutan, enam kartu ringkasan, delapan filter, grafik tren per prodi dan institusi, donat produktivitas, perbandingan KPI/hibah prodi, risk matrix empat kuadran, tabel intervensi, peringkat Top 10/25/semua, grafik mentor dan hibah, tabel matriks, kartu cluster, serta topik/SDG 2027. Proporsi kolom dan jenis grafik mengikuti contoh. Warna, tipografi, navigasi, kartu, dan footer tetap memakai tema portal BINUS saat ini.

Hero mengikuti gambar `hero.png` dari pengguna: breadcrumb, judul “Dashboard KPI Publikasi.”, pengantar, tautan Research Gallery, serta tiga ringkasan jumlah faculty member, program studi, dan tahun publikasi. Hero menggunakan gaya portal sebelumnya; sepuluh bagian dashboard di bawahnya tetap mengikuti contoh HTML. Pemeriksaan hero pada lebar 1345/390/320 piksel lolos tanpa overflow atau runtime error, dan tiga test halaman publik lolos (28 assertion). Bukti lokal: `storage/app/hero-verification.json` dan `storage/app/kpi-hero-restored-1345.png`.

Pengguna mengonfirmasi bahwa **data tetap berasal dari database/Excel**, bukan data dosen yang tertanam di HTML. JavaScript mengadaptasi payload `PublicationDashboard` ke visualisasi contoh. Tahun, jumlah dosen/prodi, skor, dan peran hibah mengikuti data aktual; daftar dosen memakai kode dosen agar nama sama tidak tercampur. Mentor, cluster, dan topik tidak disalin atau disimpulkan dari data contoh. Ringkasan pedoman matriks/cluster mengikuti contoh dan diberi keterangan sumber.

Panel rekonsiliasi workbook, rincian publikasi, katalog riset, serta filter sumber skor/cakupan tambahan dari tampilan sebelumnya telah dilepas dari halaman ini agar komposisinya kembali sesuai contoh. Dukungan impor MALANG/Raw/KPI serta perhitungan Rectorate–RTTO tetap tersedia. Skor otomatis mengikuti RTTO apabila snapshot memuat KPI, dan perhitungan sistem untuk snapshot lama. Penjelasan sumber berada di catatan bawah. Tautan pengelolaan tetap hanya untuk pengguna yang masuk.

Validasi tampilan: **26 test publikasi / 198 assertion** lolos, Blade berhasil dikompilasi, dan **28 pemeriksaan browser** lolos. Pemeriksaan membandingkan urutan sepuluh judul dan kartu dengan HTML asli, menguji data database serta workbook September, filter/Top 10/25/semua, pencarian/reset topik, cetak, viewport 320/390/768/1024 piksel, data kosong, hanya satu tahun/dosen, ambang profesor 6, dan keamanan tooltip. Tidak ada overflow halaman, koordinat SVG tidak valid, atau runtime error. Bukti lokal: `storage/app/reference-dashboard-verification.json`, `kpi-reference-restored-desktop.png`, `kpi-reference-restored-top.png`, `kpi-reference-restored-mobile.png`, dan `kpi-reference-workbook-preview.png`.

Catatan pembaruan di bawah mempertahankan riwayat implementasi impor; uraian tampilan workbook di dalamnya sudah digantikan oleh tampilan aktif di atas.

## Pembaruan 23 September 2026 — workbook MALANG dan KPI RTTO

Dashboard tetap menggunakan `/kpi-publikasi` dan mempertahankan grafik, filter, peringkat, rincian publikasi, riset, mentor, matriks, dan rencana penelitian yang sudah tersedia. Import FM sekarang mendukung bentuk `Scopus FM (September).xlsx` melalui formulir Import Rectorate yang sama.

### Sumber dan perhitungan

- **MALANG** dipilih jika ada; **Raw** menjadi fallback untuk file lama. Kedua sheet tidak digabungkan. Filter Kampus MALANG dan Submitted Non Scopus FM / Scopus FM tetap berlaku. MALANG yang kosong/tidak valid tidak diam-diam diganti Raw.
- **FIRST AUTHOR** dihitung ulang dari baris dengan Tipe Publikasi Scopus, Submitted Scopus FM, dan First Author Y, dikelompokkan menurut Kode Dosen.
- **TITLE & BOBOT** memakai Scopus + Scopus FM, lalu Count of Title dan SUM bobot asli menurut **Prodi KPI dari Excel**. Count of Title adalah jumlah kontribusi dosen–publikasi. Jumlah publikasi unik ditampilkan sebagai angka terpisah; satu publikasi dapat menyumbang beberapa kontribusi atau beberapa prodi.
- **PIVOT** menjumlahkan bobot asli menurut Kode Dosen dan Tipe Publikasi Nscopus/Scopus. Tidak memakai bobot penyesuaian quartile.
- **KPI** menyimpan daftar dosen, Jurusan Binaan, JJA, Faculty Type, Non Scopus RTTO, Scopus RTTO, dan Score KPI RTTO dari workbook. Bobot Rectorate dihitung ulang dari data publikasi, lalu bobot akhir mengambil **MAX(Rectorate, RTTO) per dosen dan per kategori**. Maksimum dihitung sebelum agregasi total.
- Rumus **Score KPI** pada seluruh baris contoh merujuk langsung ke **Score KPI RTTO**. Dashboard mengikuti rumus ini; tidak menghitung maksimum antara skor sistem dan skor RTTO. Pilihan **Perhitungan sistem** tetap menyediakan skor operasional lama dengan bobot penyesuaian.
- Jumlah first author dan status Sudah/Belum dihitung ulang. **Punya Scopus** berarti bobot Scopus akhir lebih dari nol, termasuk bila hanya RTTO yang memiliki bobot positif. Agregat RTTO tidak diubah menjadi daftar judul rekaan.

Rekap FIRST AUTHOR, TITLE & BOBOT, dan PIVOT tidak bergantung pada cache PivotTable. Pembaca XLSX mengambil nilai numerik tanpa format tampilan, sehingga pecahan tidak terpotong mengikuti format dua desimal Excel. Nilai RTTO berupa formula memakai hasil tersimpan dari Excel; simpan workbook setelah kalkulasi. Formula eksternal tidak dijalankan oleh aplikasi.

Header publikasi First Author dan Prodi KPI wajib jika workbook menyertakan sheet KPI. Header sheet KPI dipetakan berdasarkan nama, sehingga urutan kolom boleh berubah. Kode dosen duplikat, header tidak lengkap, angka RTTO negatif/tidak valid, atau skor di luar bilangan bulat 0–6 menggagalkan seluruh impor. Sel RTTO kosong disimpan NULL; nol eksplisit tetap nol. Nilai turunan KPI yang berbeda dari perhitungan dicatat pada ringkasan impor dan panel pemeriksaan sumber.

### Tampilan dan penyimpanan

Dashboard menambahkan enam kartu rekap serta empat tab: **KPI · Rectorate & RTTO**, **TITLE & BOBOT**, **FIRST AUTHOR**, dan **PIVOT**. Tabel rekonsiliasi menampilkan nilai kedua sumber, nilai maksimum, sumber yang terpilih, jumlah/status first author, Punya Scopus, dan Score KPI RTTO. Semua tabel mengikuti filter aktif dan mendukung klik nama dosen untuk membuka rincian. Mode cetak menampilkan keempat tab.

Pilihan sumber skor otomatis memakai workbook jika snapshot mempunyai sheet KPI; snapshot lama tetap memakai sistem. Rata-rata, tren, peringkat, dan status produktivitas mengikuti pilihan tersebut. Tren workbook membiarkan tahun tanpa skor RTTO kosong. Filter **Dosen sheet KPI** tersedia untuk mencocokkan populasi workbook; **Semua dosen** mempertahankan cakupan master. Dosen di luar sheet KPI tidak diberi skor RTTO otomatis. Identitas/filter master lama tetap tersedia, sementara profil dari workbook ditampilkan pada tabel rekonsiliasi.

Migrasi `2026_09_23_000000_create_publication_kpi_entries_table.php` menambah `publication_kpi_entries`, unik per batch impor dan kode dosen, dengan sumber baris dan payload audit. Migrasi telah dijalankan pada database lokal. Tidak ada paket tambahan.

Publikasi dan KPI diimpor dalam satu transaksi. Impor ulang periode yang sama mengganti keduanya; periode lain tetap tersimpan. Mengunggah file Raw tanpa KPI untuk mengganti periode yang sama juga menghapus data KPI periode tersebut agar tidak menyisakan RTTO dari file lama. Dashboard hanya menggunakan data KPI dari batch yang sesuai dengan snapshot publikasi terbaru tahun itu.

Untuk file contoh: pilih **FM / 2026 / Quarter 3 / September**, lalu unggah file XLSX melalui `/dashboard/importrectorate`. Validasi CLI tanpa menyimpan data tetap tersedia:

```powershell
php artisan publications:import-raw '<lokasi workbook.xlsx>' --year=2026 --month=9 --dry-run
```

### Verifikasi workbook September

| Rekap | Hasil |
| --- | ---: |
| Baris MALANG | 183 |
| Publikasi unik | 100 |
| Dosen dengan baris publikasi | 68 |
| Dosen sheet KPI | 113 |
| Kontribusi judul Scopus FM | 163 |
| Judul first author / dosen first author | 59 / 33 |
| Prodi KPI dengan kontribusi Scopus | 8 |
| Bobot asli Scopus Rectorate | 70,8333333333 |
| Bobot asli Nscopus Rectorate | 18,6666666667 |
| Bobot Scopus akhir, asumsi positif | 189,49 |
| Bobot Non Scopus akhir, asumsi positif | 23,1666666667 |

Impor workbook contoh diuji di SQLite sementara, tanpa memasukkan snapshot September ke database operasional. Sebanyak **1.496 perbandingan** terhadap nilai tersimpan di sheet KPI, FIRST AUTHOR, TITLE & BOBOT, dan PIVOT cocok dalam toleransi floating point 1e-9. Data master tetap mencakup 118 dosen: 113 memiliki skor workbook (termasuk 33 skor nol), lima di luar daftar KPI tetap tanpa skor RTTO.

Pengujian regresi meliputi prioritas sheet, filter gabungan first author, presisi numerik/formula, MAX per kategori, RTTO-only, NULL versus nol, header wajib, duplikat, dry-run, isolasi antarperiode, dan rollback seluruh impor. Pemeriksaan browser mencakup 23 pemeriksaan interaksi, cetak, dan viewport 320/390/768/1024 piksel tanpa overflow halaman atau runtime error. Bukti lokal: `storage/app/workbook-verification.json`, `workbook-browser-verification.json`, `workbook-kpi-desktop.png`, `workbook-kpi-mobile.png`, dan `workbook-title-bobot-desktop.png`.

Suite publikasi, riset, dan outlet lolos **46 tests / 396 assertions** melalui `php -d extension=pdo_sqlite vendor/bin/phpunit --filter 'PublicationDashboardTest|RectorateResearchTest|OutletPublikasiTest'`. Pint, pemeriksaan sintaks JavaScript/PHP, dan kompilasi Blade juga lolos. Suite lengkap masih memiliki kegagalan pada `Tests\Feature\ExampleTest`: tes Home tidak menyediakan tabel `database_dosen_new` dalam SQLite. Kegagalan tersebut berada pada `LandingController::home`, di luar perubahan publikasi.

Bagian berikut merupakan catatan integrasi sebelumnya; aturan workbook di atas berlaku untuk format baru.

Dashboard publik tersedia di `/kpi-publikasi`, tanpa login atau pemanggilan `change_perms`. URL lama `/dashboard/kpi-publikasi` mengarahkan ke URL publik dengan HTTP 301 dan mempertahankan query string. Tautannya ada di navigasi Home, Research Gallery, Perhitungan KPI, Perhitungan KPI Dosen, dan Import Rectorate.

Pembaruan 16 September 2026 memakai template Home (Constructo) melalui `resources/views/landing/layout.blade.php`, dengan header/footer bersama, warna BINUS, dan navigasi publik. Template ini dipilih karena Dashboard KPI dan Research Gallery merupakan bagian portal untuk pengunjung luar. CSS grafik dan galeri dibatasi ke konten masing-masing agar tidak memengaruhi header/footer. Tombol impor dan pedoman matriks hanya ditampilkan ketika pengguna sudah masuk. Data contoh, penetapan mentor/cluster, dan angka historis dari HTML referensi tidak diimpor sebagai fakta.

Validasi pembaruan tampilan: 28 test / 216 assertion (`PublicationDashboardTest` dan `RectorateResearchTest`) lolos, termasuk akses tamu, pengalihan URL lama, dan pembatasan impor hibah. Pemeriksaan browser mencakup filter/reset KPI, rincian dosen, pencarian publikasi, galeri/detail, menu seluler, cetak, serta lebar 320/390/768/1024 piksel tanpa overflow. Screenshot ada di `storage/app/public-kpi-desktop.png`, `storage/app/public-kpi-mobile.png`, dan `storage/app/public-research-*.png`.

## Hasil pemeriksaan sumber dan import lokal

Audit 8 September 2026 menggunakan `08 (17).xlsx`, hanya sheet **Raw**.

| Pemeriksaan | Hasil |
| --- | ---: |
| Baris Raw, tidak termasuk header | 7.237 |
| Dilewati karena kampus selain MALANG | 6.822 |
| Dilewati karena Submitted selain dua kategori FM | 232 |
| Non Scopus FM | 20 |
| Scopus FM | 163 |
| Baris dosen–publikasi yang diimpor | 183 |
| Publikasi unik, berdasarkan RequestCode | 100 |
| Dosen unik, berdasarkan Kode Dosen | 68 |
| Kode dosen tidak ditemukan dalam master | 0 |
| Duplikat dalam pilihan import | 0 |

Semua baris terpilih memiliki `Scopus Year = 2026` dan header status `St2026`. Snapshot dicatat sebagai **Agustus 2026 / Quarter 3**, dengan asumsi nama file `08` menunjukkan bulan laporan. Bulan snapshot tidak diturunkan dari tanggal pelaporan atau tanggal modifikasi file. Untuk file berikutnya, periode dipilih saat upload.

Kedua migrasi tambahan telah dijalankan pada database lokal dan 183 baris berhasil masuk. Jumlah `rectorate_dosen` sesudah import adalah **264**: 81 baris lama tetap tersimpan, ditambah 183 baris baru.

Database berisi 118 dosen. Detail master tersedia untuk 114 dosen. Singkatan prodi master lama diselaraskan dengan master detail melalui pasangan kode dosen yang sama, sehingga nama prodi dan singkatannya tidak dihitung sebagai prodi terpisah.

## Pemetaan kolom Raw

Pembaca menggunakan nama header yang dinormalisasi, bukan nomor kolom. Urutan kolom dapat berubah. Nama sheet dicocokkan tanpa membedakan huruf besar/kecil. Filter tetap Kampus `MALANG` serta Submitted `Non Scopus FM` atau `Scopus FM`.

| Header Excel | Kolom tujuan `rectorate_dosen` | Catatan |
| --- | --- | --- |
| RequestCode | request_code | Identitas publikasi; pasangan dengan kode dosen menentukan kontribusi unik |
| Author / FM Author | author / fm_author | Nama penulis dan dosen |
| Kode Dosen | kode_dosen | Penghubung master dosen |
| JJA / Pendidikan / Type | jja / pendidikan / type | Disimpan dari sumber; Type pekerjaan tidak otomatis menjadi Functional/Professional |
| S/F / Dept / Kampus | s_f / dept / kampus | Metadata sumber dan kampus hasil filter |
| First Author | first_author | Y/N dari sumber |
| Skema atau Sumber Paper | sumber_paper | Skema pelaporan publikasi, tidak otomatis dianggap hibah penelitian |
| Bobot | bobot_asli | Nilai pecahan asli; angka nol tetap nol, bukan NULL |
| Bobot + Jenis + Tipe Publikasi + Quartile Jurnal | bobot | Penyesuaian bobot sesuai kebijakan import sistem |
| Submitted | submitted | Dua kategori FM, bukan status terbit |
| St2026 atau Status | status | Header `St{tahun import}` harus cocok dengan pilihan tahun |
| Jenis / Tipe Publikasi | jenis / tipe_publikasi | Jurnal, Seminar, Book Chapter; Scopus/Nscopus |
| Title | title | Spasi akhir header diabaikan |
| Scopus Year | scopus_year | Tahun publikasi sumber, disimpan terpisah dari snapshot |
| Tanggal Pelaporan | tanggal_pelaporan | Mendukung tanggal teks dan nomor serial Excel |
| Source title | source_title | Posisi pada file contoh berbeda dari format import lama |
| Quartile Jurnal | quartile_jurnal | Terpisah dari penerbit dan notes |
| Publisher, bila ada | publisher | File contoh tidak memiliki kolom ini, sehingga NULL |
| Notes / Prodi KPI | notes / prodi_kpi | Metadata sumber tambahan |
| Semua header dan nilai baris | source_payload | JSON untuk penelusuran kembali sumber |
| Nomor baris sheet | source_row | Nomor baris Excel, termasuk posisi header |

Penyesuaian bobot: jurnal Scopus Q1/Q1-Top 10% = 3, Q2 = 2, Q3/Q4/Q2-Q3 = 1; seminar Scopus = 1. Nilai lain memakai bobot asli. Bentuk literal kategori campuran yang dikenali adalah `Q2/Q3`. Bobot penyesuaian tidak dibagi ulang menurut jumlah penulis; bobot asli tetap tersedia untuk membandingkan hasil. Implementasi mengacu pada kebijakan import yang telah ada, sekaligus memperbaiki pembacaan kolom quartile.

Validasi dilakukan sebelum perubahan database. Sheet/header wajib yang hilang, bobot tidak valid, pilihan filter kosong, atau duplikat dengan isi berbeda membuat import gagal tanpa mengganti snapshot. Import ulang periode sama mengganti snapshot secara transaksional; periode lain tetap tersimpan. Duplikat identik dihitung sekali. Tabel batch mempunyai kunci unik tahun/bulan/quarter untuk menyelaraskan import bersamaan pada periode sama. Ringkasan menampilkan jumlah tersaring, data opsional yang kosong, dan kode dosen yang belum dikenal.

## Migrasi yang sudah disediakan dan diterapkan

| Migrasi / tabel | Struktur tambahan | Fitur yang didukung |
| --- | --- | --- |
| `2026_09_08_000000_add_publication_import_tracking.php` → `publication_imports` | id; year/month/period unik; filename; SHA-256; summary JSON; timestamps | Jejak sumber file, audit hasil import, dan identitas snapshot |
| Migrasi sama → `rectorate_dosen` | publication_import_id nullable + FK; source_row unsigned integer; tanggal_pelaporan nullable date; notes nullable text; prodi_kpi nullable string; source_payload nullable JSON | Tanggal pelaporan, Prodi KPI, notes, dan penelusuran baris Raw |
| `2026_09_08_000100_create_publication_planning_tables.php` → `kpi_fm_profiles` | kode_dosen/year unik; cluster; mentor_label; source; timestamps | Penetapan cluster riset dan mentor yang eksplisit per tahun |
| Migrasi sama → `kpi_research_priorities` | kode_dosen/year berindeks; topic; sdgs JSON; source; timestamps | Satu atau lebih rencana topik/SDG per dosen untuk 2027 |

Kolom lama `month` dan `period` dapat berupa TEXT. Migrasi tidak mengubah tipe atau isi kolom tersebut, dan memakai indeks FK batch baru. Tidak ada seeding nilai mentor, cluster, atau topik dari HTML. Dashboard sudah membaca dua tabel perencanaan; formulir pengelolaan penetapan mentor dan topik belum dibuat karena sumber penetapannya belum tersedia.

## Pemetaan fitur HTML terhadap sumber sistem

| Fitur | Sumber / perilaku saat ini |
| --- | --- |
| Filter dosen/prodi/pendidikan/JJA/faculty type | `database_dosen_new`; fallback nilai valid dari `database_dosen` lalu metadata Raw untuk pendidikan/JJA |
| Capaian, tren, distribusi, peringkat, intervensi | `rectorate_dosen`, snapshot terbaru yang tersedia untuk masing-masing tahun |
| Ambang dan risk matrix | Pemilihan fungsi KPI existing berdasarkan tipe, pendidikan dan JJA; hanya skor dan ambang numerik yang tersedia diplot |
| Rincian publikasi dan pencarian | Baris snapshot sesuai dosen/prodi yang dipilih; RequestCode membedakan publikasi unik dan kontribusi penulis |
| Katalog riset | 197 rekaman `researchs`, ditautkan lewat kode dosen dan daftar researcher; identitas proyek memakai ID sistem karena satu kontrak dapat mencakup banyak proyek |
| Ketua/anggota hibah | `rectorate_research`, batch upload terbaru per tahun: 114 peran FM, 30 Ketua dan 84 Anggota, pada 45 proyek. Nilai pengakuan KPI Y/N tetap ditampilkan. Urutan researcher dalam katalog tidak dianggap sebagai peran |
| Mentor / cluster | `kpi_fm_profiles` sesuai tahun yang dipilih; saat ini kosong |
| Topik prioritas / SDG | `kpi_research_priorities`, tahun 2027; saat ini kosong |

Tahun tersedia saat audit: 2025 (snapshot Mei/Quarter 2) dan 2026 (Agustus/Quarter 3). Tahun 2023–2024 tidak dimunculkan karena tidak ada snapshot. Mei 2025 menyimpan 43 baris; 42 dapat dihubungkan ke master berkampus Malang, satu baris berkampus kosong belum terverifikasi dan tidak ikut dihitung. Seluruh baris tetap ada di database. Snapshot April 2025 tidak ditambahkan ke Mei karena laporan bulanan bersifat kumulatif.

KPI memakai fungsi sistem `TP12Func`, `AA2Func`, `L2Func`, `AA3TP3LK2Func`, `L3LK3Func`, `GBFunc`, serta padanan Professional. Parameter collection opsional memastikan dashboard hanya menghitung publikasi snapshot terpilih, tanpa query ulang seluruh tahun. Pemanggil lama tetap kompatibel. Dashboard menghitung ulang bobot dari `bobot_asli` secara baca saja agar kesalahan posisi quartile pada import lama tidak diwariskan ke dashboard.

Skor tersebut **skor operasional berdasarkan aturan sistem saat ini**, bukan skor tahunan yang disahkan. Profil master terbaru dipakai untuk semua tahun karena riwayat JJA/faculty type belum tersedia. Perhitungan existing memiliki rincian yang berbeda dari ringkasan HTML; misalnya `AA2Func` mengutamakan cabang total bobot ≥ 1,5 menjadi skor 5. Aturan ini tidak diganti dengan asumsi baru dari template. Angka dari menu kalkulator lama masih mengikuti pemilihan data implementasi menu tersebut; integrasi ini menyediakan dashboard dengan pembatasan snapshot yang eksplisit.

Semua status yang lolos filter tetap diperhitungkan sesuai perilaku import sistem. Di Excel terpilih ada 155 Accepted, 25 Published, 1 Detected, dan 2 Reviewed. Karena itu jumlah Scopus FM tidak boleh dilabeli sebagai jumlah artikel sudah terbit/terindeks.

## Data belum lengkap

- **50 dari 118 dosen** tidak memiliki baris publikasi pada snapshot Agustus 2026. Skor NULL; tidak otomatis nol atau dianggap tidak produktif.
- **183 baris Excel** tidak memiliki penerbit karena kolom Publisher tidak tersedia. Kolom penting lain pada 183 baris terpilih terisi, termasuk tanggal pelaporan setelah normalisasi serial Excel.
- **Empat profil master** belum cukup untuk menentukan matriks:

| Kode | Nama | Data yang belum tersedia/valid |
| --- | --- | --- |
| D6556 | Danang Wahyu Wicaksono | Jenjang pendidikan dan JJA |
| D6557 | Donna Carollina | Jenjang pendidikan dan JJA |
| D6916 | Fourry Handoko | Jenjang pendidikan, JJA, dan faculty type |
| D7221 | Lailal Muna Firdaus | Jenjang pendidikan, JJA, dan faculty type |

Beberapa nilai pendidikan di master lama berisi nama universitas. Nilai tersebut tidak dianggap sebagai S2/S3; pendidikan tidak ditebak dari gelar nama dosen. Huruf A/B/C dalam faculty type tidak otomatis dijadikan cluster riset.

Penetapan mentor/cluster, topik prioritas 2027, SDG rencana, dan skor historis publikasi 2023–2024 belum tersedia. Bagian dashboard menampilkan status belum tersedia. Angka 118 atau sembilan prodi dihitung dari master, bukan disalin dari template. Peran hibah telah dilengkapi melalui sheet Detail dari `07 (6).xlsx`; lihat [integrasi riset dan hibah Rectorate](rectorate-research-integration.md) untuk migrasi, galeri publik, dan audit kelengkapannya.

Jika fitur berikut diperlukan, kebutuhan lanjutan dapat dipetakan tanpa membuat data spekulatif:

| Kebutuhan berikutnya | Usulan skema / sumber |
| --- | --- |
| Skor tahunan resmi dan versi aturan | `publication_kpi_rules` (version, effective_from/to, profile, rules JSON, source) dan `publication_kpi_scores` (kode_dosen, year, rule_id, import_id, score nullable, approved_at, source), unik dosen/tahun/versi |
| Riwayat profil matriks | `lecturer_kpi_profile_histories` (kode_dosen, effective_from/to, faculty_type, education, academic_rank, source) agar perhitungan tahun lampau tidak memakai profil terkini |
| Pelibatan mahasiswa per publikasi | Relasi RequestCode ke mahasiswa dan dosen pendamping dengan identitas yang terverifikasi. Data Mahasiswa dalam file sekarang dikecualikan sesuai filter permintaan, bukan dicampur dengan FM |
| Peran hibah | Sudah diimplementasikan melalui migrasi `2026_09_08_000200`: tabel batch `research_imports` dan metadata import pada tabel khusus `rectorate_research` |
| Tata kelola cluster/mentor/topik | Formulir pengelolaan dan validasi sumber penetapan untuk dua tabel perencanaan yang sudah dibuat |

Usulan lanjutan di atas selain peran hibah belum dimigrasikan. Migrasi implementasi tidak membutuhkan paket Composer atau NPM tambahan.

## Operasional dan validasi

Upload melalui `/dashboard/importrectorate`, pilih FM, tahun, quarter dan bulan, lalu file XLSX. Opsi awal menunjuk bulan sebelumnya; tetap sesuaikan dengan periode laporan.

CLI untuk memvalidasi sebelum import:

```powershell
php artisan publications:import-raw 'C:\path\report.xlsx' --year=2026 --month=8 --dry-run
php artisan publications:import-raw 'C:\path\report.xlsx' --year=2026 --month=8
```

Pada instalasi lain yang sudah memiliki tabel aplikasi legacy:

```powershell
php artisan migrate --path=database/migrations/2026_09_08_000000_add_publication_import_tracking.php --path=database/migrations/2026_09_08_000100_create_publication_planning_tables.php
```

Validasi yang dilakukan:

- `php -d extension=pdo_sqlite vendor/bin/phpunit tests/Feature/PublicationDashboardTest.php`: **13 test / 79 assertion lolos**. SQLite diaktifkan hanya pada proses test; konfigurasi PHP sistem tidak diubah.
- Cakupan: pemilihan Raw dan header yang berubah posisi, kedua filter, bobot nol, tanggal Excel, file tidak valid, duplikat, import ulang, rollback ketika insert gagal, dry-run, quarter tidak cocok, pembatasan snapshot dan kampus, profil prodi, rencana per tahun, serta escaping payload HTML.
- Suite keseluruhan pada pemeriksaan awal integrasi publikasi: 18 dari 19 test lolos; `Tests\Feature\ExampleTest` gagal karena fixture SQLite halaman beranda tidak membuat `database_dosen_new`. Ini merupakan keterbatasan fixture yang sudah ada. Validasi gabungan setelah penambahan hibah: **32 test / 264 assertion lolos**, mencakup RectorateResearchTest, PublicationDashboardTest, dan OutletPublikasiTest.
- Browser Chrome: HTTP 200, tanpa exception JavaScript; filter tahun/prodi/dosen, rincian, pencarian kosong, reset, dan viewport 390px berhasil. Screenshot lokal tersedia di `storage/app/publication-dashboard-desktop.png` dan `storage/app/publication-dashboard-mobile.png`.
- `php artisan view:cache`, `node --check public/js/publication-kpi.js`, Pint untuk file PHP baru, dan `git diff --check` berhasil.

File Excel sumber tetap berada di lokasi semula. Import tidak menyalinnya ke direktori public. Nama file, hash, ringkasan, metadata, dan payload setiap baris terpilih tersimpan di database.
