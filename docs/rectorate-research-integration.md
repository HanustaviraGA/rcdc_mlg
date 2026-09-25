# Integrasi riset dan hibah Rectorate

Implementasi 8 September 2026 menambahkan upload laporan hibah, galeri publik, bagian Research Gallery di beranda, dan peran hibah pada dashboard KPI publikasi. Judul, peneliti, pendanaan, SDG, dan angka contoh dalam HTML referensi tidak dijadikan data.

Pembaruan 16 September 2026 menyatukan galeri dan detail riset dengan template Home (Constructo), bersama Dashboard KPI publik di `/kpi-publikasi`. Keduanya memakai logo, navigasi, footer, tipografi, dan palet yang sama melalui layout Blade bersama. Galeri dan detail dapat dibuka tanpa login atau `change_perms`. Form `/dashboard/import-hibah` dan POST `/research-imports` memerlukan sesi login seperti akses dashboard; permintaan tamu mendapat HTTP 403 sebelum validasi atau impor berlangsung.

## Hasil import lokal

Sumber: `C:\Users\hanustavira.acarya\Downloads\07 (6).xlsx`. Pembaca hanya menggunakan sheet **Detail**, dengan filter **Lokasi Kampus = Binus @Malang**. Pencocokan mengabaikan huruf besar/kecil dan spasi berlebih, tetapi tidak menerima kampus lain yang sekadar mengandung kata Malang. Tidak ada filter tambahan berdasarkan kategori atau pengakuan KPI.

| Pemeriksaan | Hasil |
| --- | ---: |
| Baris data Detail | 2.472 |
| Baris kampus lain yang dilewati | 2.358 |
| Baris terpilih dan tersimpan | 114 |
| Proyek, berdasarkan Tahun Anggaran + Kd. Prop | 45 |
| Peneliti unik, berdasarkan Kode Dosen/NIM | 66 |
| Peran Ketua | 30 |
| Peran Anggota, semua urutan | 84 |
| Kode FM tidak ditemukan dalam master | 0 |
| Duplikat identitas proyek/peneliti/peran | 0 |

| Tahun anggaran | Baris peran | Proyek |
| --- | ---: | ---: |
| 2023 | 4 | 4 |
| 2024 | 9 | 5 |
| 2025 | 1 | 1 |
| 2026 | 100 | 35 |

Seluruh 114 baris terpilih berkategori FM. Kolom **Pengakuan KPI Research Program** berisi **113 Y dan 1 N**. Baris N pada tahun 2025 merupakan insentif publikasi menurut sumber; tetap disimpan karena lolos filter kampus. Jumlah 45 adalah proyek/entri menurut identitas laporan, bukan klaim bahwa semuanya diakui sebagai KPI research. Tabel peran pada dashboard menampilkan nilai Y/N tersebut.

Migrasi telah diterapkan dan file berhasil diimpor sebagai batch pertama. Tabel `rectorate_research` kini berisi **114 baris**, sedangkan **197 baris `researchs` tetap utuh**. Data publikasi `rectorate_dosen` tetap 264 baris dari integrasi sebelumnya.

## Pemisahan database dan migrasi

Database lokal sudah memiliki tabel khusus `rectorate_research` yang masih kosong, dengan 54 kolom sumber berbentuk TEXT. Implementasi memperluas tabel ini. Tabel `researchs` tetap menjadi sumber katalog dari sistem dan tidak diubah skema maupun isinya oleh importer.

Migrasi: `database/migrations/2026_09_08_000200_add_rectorate_research_imports.php`.

| Tabel / kolom | Tipe dan relasi | Kegunaan |
| --- | --- | --- |
| `research_imports` | id; filename; sha256 unik; sheet; campus; summary JSON; timestamps | Riwayat file, identitas isi file, hasil filter dan audit kelengkapan |
| `rectorate_research.research_import_id` | FK nullable ke research_imports, restrict delete | Menghubungkan setiap baris dengan upload asal |
| `budget_year` | unsigned small integer nullable, berindeks | Tahun anggaran numerik untuk memilih laporan aktif |
| `project_key` | string 64 nullable, berindeks | SHA-256 tahun anggaran + kode proposal |
| `row_key` | string 64 nullable; unik bersama research_import_id | SHA-256 proyek + kode peneliti + peran; mencegah kontribusi ganda dalam satu batch |
| `source_row` | unsigned integer nullable | Nomor baris sheet untuk pemeriksaan |
| `starts_on`, `ends_on` | date nullable | Tanggal yang berhasil dinormalisasi |
| `approved_amount`, `internal_amount`, `program_amount` | decimal(20,2) nullable | Nominal numerik dari Dana Disetujui, Hibah Internal Binus, Hibah per Prodi |
| `sdg_numbers` | JSON nullable | Nomor SDG yang eksplisit dalam sumber |
| `data_issues` | JSON nullable | Daftar nilai yang gagal dinormalisasi atau rentang tanggal bermasalah |
| `source_payload` | JSON nullable | Seluruh pasangan header dan nilai baris terpilih |

Jika tabel rectorate belum ada pada instalasi tujuan, migrasi juga membuat 54 kolom sumber dan primary key `id_research`. Kolom baru nullable agar kompatibel dengan data legacy. Baris legacy tanpa batch tidak otomatis masuk galeri karena sumber upload dan tahun aktifnya belum terverifikasi.

Rollback menghapus tabel batch dan kolom tambahan, termasuk metadata audit, tetapi mempertahankan tabel rectorate beserta kolom dan baris sumber lama. Rollback tidak dijalankan pada database lokal.

## Pemetaan kolom Detail

Semua 54 header dipetakan di konstanta `RectorateResearchReader::HEADERS`. Pembaca memakai nama header, bukan posisi kolom. Header dengan baris baru, seperti Bobot sumber dana, dikenali setelah normalisasi spasi.

| Kelompok header Excel | Kolom sumber rectorate / penggunaan |
| --- | --- |
| No, Tahun Anggaran, Kd. Prop | no, tahun_anggaran, kd_prop; identitas laporan dan proyek |
| Kode Dosen/NIM, NIDN, Nama, Peran, Kategori FM/eksternal/Mahasiswa | kode_dosen_nim, nidn, nama, peran, kategori_fm_eksternal_mahasiswa; identitas dan peran yang eksplisit |
| Pengakuan KPI Research Program, Pengakuan KPI non Tuition, Bobot sumber dana, Bobot Peran, Bobot sumber x bobot peran | pengakuan_kpi_research_program, pengakuan_kpi_non_tuition, bobot_sumber_dana, bobot_peran, bobot_sumber_x_bobot_peran; nilai sumber dipertahankan |
| Prodi di KPI, Fakultas KPI, Prodi/Jur Binaan, Prodi PDPT, RIG/BDSRC/FBRC, Lokasi Kampus | prodi_di_kpi, fakultas_kpi, prodi_jur_binaan, prodi_pdpt, rig_bdsrc_fbrc, lokasi_kampus |
| Tahun ke-, Total tahun Penelitian, Tanggal Mulai, Tanggal Selesai | tahun_ke, total_tahun_penelitian, tanggal_mulai, tanggal_selesai; tanggal numerik tersedia di kolom tambahan |
| Sumber Dana, Sumber Pemberi Hibah, Jenis Institusi pemberi Hibah, Nama Pemberi hibah, Program Hibah, SKEMA | sumber_dana, sumber_pemberi_hibah, jenis_institusi_pemberi_hibah, nama_pemberi_hibah, program_hibah, skema |
| Status Usulan, JUDUL, Jenis Penelitian, TKT | status_usulan, judul, jenis_penelitian, tkt |
| Nilai Hibah selain Rupiah, Nama Mata Uang, Nilai Tukar to Rupiah, Hibah Internal Binus, Dana Disetujui, Hibah per Prodi | nilai_hibah_selain_rupiah, nama_mata_uang, nilai_tukar_to_rupiah, hibah_internal_binus, dana_disetujui, hibah_per_prodi; tidak mengarang konversi kurs |
| SDGs, Keyword SDGs, Bidang Ilmu by QS Subject, Subtopik Research Roadmap, Produk yang Dihasilkan | sdgs, keyword_sdgs, bidang_ilmu_by_qs_subject, subtopik_research_roadmap, produk_yang_dihasilkan |
| Bidang Penelitian, Sub Bidang Penelitian, Tujuan Sosial Ekonomi, SubTujuan Sosial Ekonomi | bidang_penelitian, sub_bidang_penelitian, tujuan_sosial_ekonomi, sub_tujuan_sosial_ekonomi |
| Email Peneliti luar, Keterangan, Mitra, Nama Mitra, Email Mitra, Multi Disiplin | email_peneliti_luar, keterangan, mitra, nama_mitra, email_mitra, multi_disiplin |

Tahun, kode proposal, kode peneliti, nama, peran, judul, serta header kampus wajib tersedia. Nilai opsional kosong atau tanda `-` disimpan NULL; nol tetap nol. Nominal dan tanggal yang tidak terbaca tetap tersedia dalam kolom sumber/payload, sedangkan hasil normalisasinya NULL dan dicatat sebagai masalah. NIDN, alamat email, dan payload mentah tidak dikirim ke galeri publik.

## Perilaku upload dan penggabungan tampilan

- File diperiksa sebelum transaksi penulisan. Sheet/header wajib hilang, filter kosong, atau identitas ganda dengan isi berbeda menggagalkan import tanpa mengganti data aktif.
- File dengan isi identik, termasuk jika diganti nama, tidak membuat batch atau baris baru. Duplikat identik dalam satu file dihitung sekali.
- Setiap file berbeda menjadi batch baru. Galeri dan KPI memakai **batch upload terbaru untuk masing-masing tahun anggaran**, bukan menjumlahkan seluruh riwayat upload.
- Upload harus merupakan laporan lengkap untuk tahun-tahun yang tercantum. Misalnya, upload yang hanya memuat 2026 mengganti tampilan data 2026; batch aktif 2023–2025 tetap dipakai. Baris dari batch sebelumnya tetap tersimpan untuk audit.
- Urutan terbaru mengikuti ID batch upload, bukan angka `07` dalam nama file atau tanggal modifikasi XLSX. Mengunggah file lama yang berbeda isi akan menjadikannya batch terbaru untuk tahun tersebut.
- Satu proyek rectorate menggabungkan baris peneliti berdasarkan tahun + kode proposal. Dana yang sama pada beberapa anggota ditampilkan sekali, tidak dijumlahkan berulang.
- Proyek dari sistem memakai **ID `researchs`**. Nomor kontrak tidak dipakai untuk menyatukan proyek karena satu kontrak dapat mencakup banyak judul berbeda.
- Proyek yang ada di kedua sumber tetap menjadi dua entri berlabel sumber. Tidak ada penyatuan otomatis berdasarkan kemiripan judul.

## Tampilan yang tersedia

- `/research-gallery`: pilihan sumber Rectorate/Sistem Riset/Semua, pencarian, filter tahun/bidang/fakultas/peneliti, urutan, dan paginasi. Sumber awal Rectorate.
- Detail proyek: metadata, peneliti dan peran, dana, periode, SDG, produk/roadmap yang tersedia, serta catatan perbedaan nilai. Tombol cetak memakai fasilitas cetak/simpan PDF browser.
- `/`: empat proyek rectorate terbaru menurut tahun; beralih ke sumber sistem jika rectorate belum tersedia.
- `/dashboard/importrectorate`: halaman upload FM, MHS, dan hibah, ringkasan import, serta kelengkapan sumber per bulan. Alamat lama `/dashboard/import-hibah` mengarahkan ke modul ini. POST utama berada di `/backoffice/importrectorate/upload`; `/research-imports` tetap kompatibel.
- `/kpi-publikasi`: peran hibah FM dari batch aktif, termasuk tahun dan pengakuan KPI Y/N. URL lama `/dashboard/kpi-publikasi` mengarahkan ke URL ini. Peran penelitian tidak ditebak dari urutan nama pada tabel `researchs`.

Galeri menggunakan ilustrasi geometris dan metadata nyata sebagai sampul. Abstrak, metode, foto kegiatan, hasil terukur, dan dampak yang tidak tersedia tidak diambil dari contoh HTML. SDG yang sudah dilaporkan tidak otomatis menjadi rencana prioritas 2027.

## Data belum lengkap dan perlu konfirmasi

| Kolom pada 114 baris sumber | Baris kosong |
| --- | ---: |
| Produk yang Dihasilkan | 93 |
| Keyword SDGs | 62 |
| Program Hibah | 13 |
| SKEMA | 11 |
| Dana Disetujui | 10 |
| Tanggal Mulai | 2 |
| Tanggal Selesai | 2 |
| Bidang Ilmu by QS Subject | 2 |
| SDGs | 1 |

Setelah anggota dikelompokkan, **8 proyek belum memiliki nominal dana disetujui**, **1 proyek belum memiliki SDG**, dan **1 proyek belum memiliki periode penelitian**. Kekosongan dalam satu baris dapat dilengkapi oleh nilai yang tersedia pada anggota proyek yang sama.

**14 proyek memiliki metadata yang berbeda antarbaris**, terutama Program Hibah. Beberapa nilai Program Hibah berbentuk angka seperti 2039/2040; aplikasi mempertahankan nilai tersebut dan menampilkan bagian “Beberapa metadata perlu konfirmasi”. Nominal atau tanggal yang saling berbeda tidak dipilih secara diam-diam. SDG, bidang ilmu, dan produk ditampilkan sebagai gabungan nilai yang tercatat pada anggota.

Tim hanya mencakup baris kampus Malang. Tidak adanya Ketua pada suatu proyek tidak langsung berarti datanya rusak: Ketua dari kampus lain dapat berada di luar filter. Anggota lintas kampus tidak ditambahkan karena pengguna meminta filter khusus Binus @Malang.

## Operasional dan validasi

Untuk instalasi lain:

```powershell
php artisan migrate --path=database/migrations/2026_09_08_000200_add_rectorate_research_imports.php
php artisan research:import-rectorate 'C:\path\laporan.xlsx' --dry-run
php artisan research:import-rectorate 'C:\path\laporan.xlsx'
```

Upload web menerima XLSX hingga 20 MB. POST `/research-imports` menggunakan CSRF web Laravel. File sumber tidak disalin ke direktori public. Implementasi memakai pembaca XLSX yang sudah terpasang dan tidak menambah dependensi Composer/NPM.

Validasi:

```powershell
php -d extension=pdo_sqlite vendor/bin/phpunit tests/Feature/RectorateResearchTest.php tests/Feature/PublicationDashboardTest.php tests/Feature/OutletPublikasiTest.php
```

**32 test, 264 assertion lolos**. Pengujian mencakup filter tepat, urutan header, normalisasi, validasi file, duplikat, import ulang, transaksi/rollback, batch per tahun, pemisahan `researchs`, dana proyek, konflik metadata, filter galeri, detail/404, escaping, payload publik, endpoint upload, dan dry-run. SQLite diaktifkan hanya untuk proses test.

Browser Chrome berhasil memeriksa sumber, tahun, pencarian, detail, hasil kosong, paginasi, ringkasan KPI, riwayat upload, galeri beranda, dan lebar ponsel 390 px tanpa overflow horizontal atau exception JavaScript. Screenshot lokal ada di `storage/app/research-gallery-desktop.png`, `research-gallery-collection.png`, `research-gallery-detail.png`, `research-gallery-homepage.png`, dan `research-gallery-mobile.png`.

Pemeriksaan kompilasi Blade, sintaks JavaScript/PHP, format PHP baru, dan whitespace diff juga dilakukan. File audit dan screenshot di `storage/app` merupakan artefak lokal yang tidak dimasukkan ke Git.
