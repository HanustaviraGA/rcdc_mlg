# Analisis integrasi Performance Dashboard

Tanggal pemeriksaan: 20 Oktober 2026. Acuan: `performance_dashboard_indikator_alokasi_budget_vrtt.html`.

Tampilan acuan dapat diimplementasikan, tetapi data contoh, rumus, dan penyimpanannya belum dapat langsung dipakai sebagai dashboard resmi. Publikasi FM dan mahasiswa sudah mempunyai sumber yang cukup untuk tahap awal; Research Program mempunyai data dasar. Indikator lain masih membutuhkan sumber atau input pengelola.

Dokumen ini merupakan analisis sebelum implementasi. Pemeriksaan dilakukan pada kode, route Laravel, struktur database, metadata impor, dan keluaran service lokal. Tidak ada perubahan controller, route, Blade, atau data bisnis selama analisis ini.

## Keputusan pengguna

- Q1–Q4 menampilkan **capaian kumulatif sampai akhir kuartal**.
- Sumber pagu dan realisasi **Budget VRTT belum tersedia**, sehingga diperlukan **input manual**.
- Blade lama dipertahankan sebagai `resources/views/dashboard/publication_kpi_old.blade.php`.
- Implementasi baru dapat memakai controller tersendiri agar controller lama tetap utuh.

## Cakupan HTML acuan

HTML menyediakan tujuh pilihan tampilan: Publication FM, Publikasi Mahasiswa, New Citation, Research Program, New IP, NTR, dan Alokasi Budget VRTT. FM dan mahasiswa memakai satu kelompok dataset `publication`; indikator lain memakai kelompok masing-masing.

Fitur lain mencakup pemilihan beberapa tahun, kuartal, empat kartu ringkasan, grafik per jurusan, skor, GAP, rincian dosen/publikasi, pencarian, pengurutan, ekspor CSV, impor Excel, editor data, serta backup JSON.

Dataset bawaan hanya berisi **publikasi 2026** dengan kartu contoh EBC/CS/DKV. Dataset sitasi, IP, NTR, riset, dan VRTT belum diisi. Angka 1.250/1.255, target 14, realisasi 17, skor 6, dan persentase contoh bukan hasil pembacaan database aplikasi. Tanggal update 22 Oktober 2026 juga merupakan teks bawaan, bukan waktu pembaruan sumber.

## Ketersediaan data

| Bagian | Sumber yang sudah ada | Kekurangan atau penyesuaian |
| --- | --- | --- |
| Publikasi FM | `rectorate_dosen`, `publication_imports`, `publication_kpi_entries`; profil dosen; metadata `report.realization` dan `report.ranges` | Pisahkan realisasi resmi prodi, jumlah judul, bobot, dan KPI dosen. Target kuartal belum memiliki penyimpanan khusus. |
| Publikasi mahasiswa | `student_publication_imports`: `records`, `publication_list`, `summary.title_counts`, `summary.list_counts`, dan metadata target/skor | Belum masuk ke service dashboard lama. Ada selisih angka sumber yang harus ditampilkan atau ditetapkan sumber resminya. Jangan mengambil seluruhnya dari `rectorate_mahasiswa` lama. |
| Research Program | `rectorate_research`, `research_imports`, `RectorateResearchRepository`; proposal, tahun anggaran, peran, pengakuan KPI, prodi, bobot | Tentukan apakah indikator menghitung proyek, pengakuan KPI, atau bobot. Target/skor resmi dan atribusi proyek lintas prodi masih perlu ditentukan. |
| Budget VRTT | Pengguna menetapkan input manual; tabel hibah mempunyai nominal persetujuan/internal/prodi | Nominal hibah belum membuktikan pagu maupun pengeluaran VRTT. Perlu input pagu, revisi, realisasi kumulatif, periode, prodi, bukti/catatan, dan pencatat. |
| New Citation | Ada tautan profil Scopus/Google Scholar di `identitas_dosen` | Belum ditemukan jumlah sitasi bertanggal dan baseline untuk menghitung sitasi baru. Perlu sumber yang disepakati, identitas penulis/publikasi, serta snapshot pembanding. |
| New IP | Belum ditemukan sumber terstruktur khusus IP | Perlu identitas/nomor IP, jenis, pemilik, prodi, tanggal, status pengakuan, serta target. Teks produk hasil riset belum cukup untuk menyatakan IP baru. |
| NTR | Hibah memiliki `pengakuan_kpi_non_tuition` Y/N, sumber dana, dan nominal | Bisa menjadi masukan sebagian indikator, tetapi belum membuktikan seluruh pendapatan NTR atau penerimaan uang. Tentukan cakupan, basis pengakuan, nilai yang dipakai, alokasi prodi, dan target. |
| Milestone | Empat kartu ringkasan pada HTML, dengan angka contoh | Definisi, satuan, cakupan organisasi, sumber, target tahunan/kuartal, dan hubungannya dengan publikasi belum ditetapkan. Jangan menyimpulkannya sebagai penjumlahan FM dan mahasiswa. |

Sumber perhitungan yang bisa dipakai ulang: `PublicationWorkbookMetrics`, `ReportWorkbookReader`, `MonthlyPublicationReport`, `StudentPublicationImporter`, dan `RectorateResearchRepository`. Metadata target/skor sudah dibaca oleh importer; tidak perlu membangun pembaca Excel kedua di browser.

## Temuan pada database lokal

Angka berikut merupakan kondisi database saat diperiksa, bukan jaminan untuk lingkungan lain atau seluruh baris unik lintas periode.

| Sumber | Kondisi terverifikasi |
| --- | --- |
| Snapshot publikasi 2025 yang dipilih service lama | Mei/Q2; 42 baris kontribusi dosen–publikasi; belum ada sheet KPI; tanggal pelaporan kosong pada 42 baris lama |
| Snapshot publikasi 2026 yang dipilih service lama | September/Q3; 183 baris kontribusi; 113 entri KPI; tidak ada duplikat dalam snapshot terpilih |
| Impor FM dengan pelacakan | Agustus dan September 2026. Agustus belum mempunyai metadata target/skor; September sudah mempunyai keduanya. |
| Rekap FM September | Target kampus 89; realisasi resmi sekitar 67,9333 bobot |
| Impor mahasiswa | September 2026; 106 baris, terdiri atas 102 Scopus dan 4 Non Scopus; TITLE/LIST berisi 102 entri |
| Rekap mahasiswa September | Target kampus 176; Realization Template menyebut 103, sedangkan TITLE/LIST 102. CS: Realization Template 30, TITLE/LIST 29. Peringatan selisih sudah tersimpan. |
| Hibah yang aktif dalam dashboard | 45 proyek: 2023 sebanyak 4, 2024 sebanyak 5, 2025 sebanyak 1, 2026 sebanyak 35 |
| Cakupan dosen | Master baru 120 baris; gabungan dosen pada service lama 123. Gabungan master/KPI/hibah tidak otomatis sama dengan populasi FM aktif untuk persentase. |
| Data mahasiswa lama | `rectorate_mahasiswa` hanya 10 baris, sehingga tidak mewakili impor mahasiswa September yang baru. |

Pilihan tahun 2023–2024 pada dashboard lama berasal dari hibah, bukan bukti adanya publikasi/KPI tahun tersebut. Ketiadaan publikasi harus tampil sebagai **belum tersedia**, bukan nol.

Dua selisih lain perlu diperhatikan:

- Target FM enam prodi pada metadata September berjumlah 77, sedangkan target kampus 89. Target kampus tidak boleh ditimpa dengan hasil penjumlahan kartu prodi; alokasi selisih 12 perlu dijelaskan jika semua target harus direkonsiliasi.
- Jumlah bobot Scopus KPI dosen memakai `MAX(Rectorate, RTTO)` per dosen. Pada data 2026 hasil penjumlahannya sekitar 189,49, berbeda dari realisasi resmi prodi/kampus 67,9333. Kedua angka mengukur hal berbeda dan harus mempunyai label/sumber berbeda.

Selisih mahasiswa 102/103 juga sudah didokumentasikan pada `docs/monthly-uploads-and-reports.md`. Pilihan awal yang konsisten dengan laporan aplikasi adalah TITLE/LIST 102 dan CS 29, disertai catatan angka Realization Template. Penetapan angka resmi yang berbeda harus menjadi override yang dapat ditelusuri.

## Aturan perhitungan dan periode

### Kuartal kumulatif

- Q1 mencakup sampai Maret, Q2 sampai Juni, Q3 sampai September, dan Q4 sampai Desember dalam tahun terkait.
- Pilih snapshot kumulatif terbaru yang tidak melewati batas kuartal. Jangan menjumlahkan snapshot Agustus dan September, maupun menjumlahkan capaian Q1, Q2, Q3, dan Q4 sebagai capaian tahunan.
- Cantumkan bulan sumber yang sebenarnya. Data Mei untuk Q2 belum membuktikan kondisi akhir Juni; data Q3 tidak boleh diberi label final Q4.
- Jika snapshot belum mencapai akhir kuartal, tandai sebagai capaian sementara sampai bulan yang tersedia. Jika tidak ada snapshot yang sesuai, tampilkan belum tersedia.
- Tampilan semua kuartal/tahunan memakai snapshot terbaru yang tersedia dalam tahun tersebut dengan label cakupan, bukan penjumlahan snapshot.
- Target kumulatif setiap kuartal perlu diambil dari sumber resmi atau diisi pengelola. Target tahunan tidak otomatis dibagi empat.
- `period` pada impor berhubungan dengan bulan laporan. Jangan menyamakannya dengan `quartile_jurnal` Q1–Q4.
- `year` laporan, `scopus_year`, `tanggal_pelaporan`, dan tahun anggaran riset perlu dipisahkan. Mengganti tahun laporan dengan Scopus Year dari importer browser dapat mengubah hasil historis.

Service `PublicationDashboard::data()` saat ini hanya mengambil snapshot publikasi terakhir per tahun. Filter kuartal perlu pembacaan sumber baru di service khusus, tanpa mengubah perilaku service lama.

### Bobot, judul, skor, dan persentase

- Realisasi resmi FM prodi/kampus mengikuti metadata workbook jika tersedia. Bobot asli `bobot_asli`, bobot penyesuaian `bobot`, dan bobot KPI dosen `MAX(Rectorate, RTTO)` mempunyai kegunaan berbeda.
- Jumlah baris kontribusi dosen, jumlah judul unik, dan jumlah judul mahasiswa tidak dapat dipertukarkan. Identitas `request_code` dan kode penulis harus dipertahankan untuk deduplikasi.
- Skor KPI dosen mengikuti Score KPI RTTO bila tersedia. Skor prodi mengikuti rentang `Scoring PI` prodi. Rata-rata skor dosen bukan otomatis skor prodi.
- Ketiadaan nilai tetap `null`/belum tersedia. Nol hanya dipakai ketika sumber memang menyatakan nol.
- Persentase FM memiliki Scopus membutuhkan definisi pembilang dan populasi: tahun berjalan atau seluruh riwayat, FM aktif atau termasuk AFM, serta perlakuan dosen keluar/tersembunyi/dihapus. Master memiliki status, `is_hidden`, dan `deleted_at`, tetapi tidak menyediakan roster historis lengkap per kuartal.
- Persentase beberapa tahun tidak dijumlahkan. Tampilkan per tahun sampai definisi penggabungan disepakati, termasuk apakah orang yang sama dihitung sekali atau sebagai dosen-tahun.
- Status Accepted/Reviewed saat ini ikut dihitung oleh kebijakan impor lama. Label Scopus tidak otomatis berarti seluruhnya telah terbit. Pada snapshot terpilih ada Published, Accepted, Detected, dan Reviewed.
- Daftar program studi harus dinamis. Pemetaan EBC pada HTML perlu diselaraskan dengan BC pada aplikasi; pertahankan satu kode kanonis dan alias tampilan. HTML hanya menyiapkan tiga prodi, sedangkan aplikasi memiliki lebih banyak prodi/unit.

### Budget VRTT manual

Rancangan awal yang sesuai jawaban pengguna adalah input nilai kumulatif per tahun, kuartal, dan prodi. Simpan nilai numerik rupiah, tanggal berlaku/cutoff, pagu yang disahkan, revisi bila ada, realisasi kumulatif, catatan/bukti, pengguna pencatat, dan waktu perubahan.

Rumus dasar yang dapat dipakai setelah arti pagu ditetapkan:

- Sisa anggaran = pagu yang berlaku − realisasi kumulatif.
- Serapan = realisasi kumulatif ÷ pagu yang berlaku × 100%, hanya jika pagu lebih dari nol.
- Pagu kosong/serapan yang belum dapat dihitung ditampilkan belum tersedia. Realisasi yang melebihi pagu perlu terlihat, tidak dipotong menjadi 100%.

Penjumlahan antarprodi hanya berlaku untuk alokasi yang tidak saling tumpang tindih. Pagu/realisasi kumulatif antar-kuartal tidak dijumlahkan. Jika nanti memakai transaksi pengeluaran, jangan menambah transaksi tersebut lagi di atas nilai kumulatif manual yang sudah mencakupnya.

Empat judul ringkasan VRTT di HTML masih menumpang struktur tujuh nilai publikasi. Sebelum implementasi, tetapkan isi setiap kartu dan satuannya; misalnya pagu, realisasi, sisa, dan persentase serapan. Definisi target budget dan alokasi budget perlu dibuat jelas agar tidak menyajikan dua istilah untuk angka yang sama tanpa penjelasan.

## Kekurangan teknis HTML

1. Semua penyimpanan editor memakai `localStorage`. Data hanya tersedia pada browser/origin tersebut, tidak menjadi data bersama dan tidak mempunyai otorisasi server atau riwayat perubahan. Backup JSON merupakan pemindahan data, bukan sinkronisasi.
2. Tombol Kelola Data hanya mengubah variabel `adminMode`; siapa pun yang membuka HTML dapat mengedit data lokalnya. Implementasi Laravel memerlukan pemeriksaan hak pengelola di server, validasi, dan proteksi CSRF untuk penulisan berbasis sesi.
3. Impor Excel browser memakai kunci kode dosen + judul + tahun, berbeda dari identitas RequestCode + kode dosen pada importer aplikasi. Parser acuan juga tidak menerapkan kebijakan seleksi kampus/Submitted yang sama. Gunakan ulang jalur impor aplikasi.
4. Impor dan edit rincian tidak otomatis menghitung ulang seluruh grafik/ringkasan/skor. Nilai dapat tidak konsisten. Bedakan nilai turunan otomatis dan override resmi dengan alasan/sumber perubahan.
5. Filter kuartal hanya memilih kartu dengan label `quarter`; belum memilih snapshot kumulatif. Dataset contoh tidak mengisi label quarter sehingga filter Q1–Q4 dapat kosong.
6. Detail semua indikator diarahkan ke tabel dosen. Mahasiswa membutuhkan daftar penulis mahasiswa/judul, riset membutuhkan proyek, dan VRTT membutuhkan rincian anggaran. Kartu indikator lain juga masih memakai label detail Scopus.
7. Tahun pilihan dan parser tahun dibatasi 2023–2026. Tahun harus dihasilkan dari data dan kebutuhan perencanaan, termasuk saat memasuki 2027.
8. CSS memuat selector global seperti `body`, `select`, `button`, `.modal`, serta variabel `:root`. Beri cakupan CSS pada pembungkus halaman agar tidak bentrok dengan Bootstrap, header, footer, dan halaman portal lain.
9. Tanggal update perlu berasal dari sumber tersimpan, terpisah dari tanggal cutoff laporan. Jika sumber FM/MHS/VRTT berbeda waktunya, tunjukkan status masing-masing.
10. Nilai yang diimpor/diedit perlu dirender dengan escaping yang konsisten; beberapa angka pada template HTML dimasukkan langsung melalui `innerHTML`. Nilai uang, jumlah, bobot, dan persentase juga memerlukan format yang berbeda.

## Kondisi rancangan baru yang sudah ada di workspace

File controller, service, CSS, JS, dan route remodel sudah ada saat analisis dimulai. File tersebut belum merupakan integrasi yang selesai.

| Temuan | Dampak |
| --- | --- |
| `/kpi-publikasi` masih menuju `PublicationDashboardController::index`, yang memanggil `dashboard.publication_kpi` | Pemeriksaan `view()->exists()` menyatakan view itu tidak ada; hanya `dashboard.publication_kpi_old` yang ada. Penggantian nama Blade saja belum mempertahankan akses halaman. |
| Controller remodel memanggil `dashboard.publikasi_kpi` | View ini juga belum ada. Nama berbeda dari pola `publication_kpi` yang dipakai sebelumnya. |
| `routes/api_remodeled_dashboard.php` belum terdaftar pada routing aplikasi | `route:list --path=api/publication-dashboard` tidak menemukan route. `/kpi-publikasi-old` yang disebut komentar controller juga belum terdaftar. |
| `PublicationDashboardRemodeledService::buildSummary()` membuat 11 elemen yang diawali judul | HTML mengharapkan tujuh nilai, sementara empat judul berada di properti `titles`. Kontrak datanya belum sesuai. |
| `$yearPublications = $publications` tanpa filter tahun | Publikasi lintas tahun berpotensi masuk ke setiap dataset tahun, termasuk tahun yang sebenarnya hanya mempunyai hibah. |
| Kartu dan tabel faculty mengisi bobot/realisasi/first author dengan nol atau skor kosong | Belum memakai perhitungan/sumber yang tersedia; nilai kosong dapat disalahartikan sebagai capaian nol. |
| Service hanya membangun dataset publication/FM | Data mahasiswa, riset, sitasi, IP, NTR, dan VRTT belum ditransformasikan. |
| Payload publikasi service lama membuang `author`, `fm_author`, `s_f`, `kampus`, `sumber_paper`, dan `scopus_year` | Sebagian kolom detail akan kosong meskipun database menyimpannya. Service baru sebaiknya membaca field yang diperlukan secara eksplisit; tanggal/status juga perlu dipetakan. |
| JavaScript remodel masih berisi `initialData` contoh dan membaca `localStorage` | Belum mengonsumsi hasil service Laravel. Memasang file JS saja tetap menampilkan data contoh/browser. |
| `getSummary()` mengembalikan nilai `null`; `saveSummary()` memberi respons berhasil tanpa penyimpanan | Endpoint masih placeholder dan belum bisa dipakai untuk data bersama. |
| `getDepartmentDetail()` tidak memakai tahun yang divalidasi; filter `getFacultyPublications()` hanya menguji apakah request code dan kode masukan bernilai benar | Hasil tidak dibatasi dengan benar menurut dosen, tahun, dan prodi. Filter publikasi harus membandingkan `kode_dosen` dengan kode yang diminta. |
| Route menunjuk `saveCard()` dan `deleteCard()` yang belum ada | Aksi tersebut belum dapat dijalankan. |
| `saveSummary()` memakai `$this->authorize('isAdmin')`, sedangkan base controller tidak memakai trait otorisasi dan Gate tersebut belum ditemukan | Otorisasi perlu diselaraskan dengan mekanisme hak akses aplikasi; login saja belum menetapkan hak mengelola. |

## Pemisahan implementasi yang disarankan

- Pakai controller khusus; nama `PublicationDashboardRemodelController` yang sudah ada dapat dilanjutkan setelah diperbaiki. Pertahankan `PublicationDashboardController` lama.
- Buat service khusus untuk pemilihan snapshot kumulatif, rekap FM/MHS, dan transformasi tiap indikator. Gunakan helper perhitungan yang ada; jangan mengubah kontrak `PublicationDashboard::data()` lama demi tampilan baru.
- Blade baru memakai nama konsisten, misalnya `dashboard.publication_kpi`, dan menerima payload server yang sudah siap ditampilkan. Blade `_old` tetap utuh.
- Saat pengalihan dilakukan, `/kpi-publikasi` menggunakan controller baru dan nama route `publication-dashboard` tetap dipertahankan. Tambahkan route arsip, misalnya `/kpi-publikasi-old`, memakai wrapper/closure khusus yang merender `_old` dengan data service lama. Route arsip tidak bisa langsung memakai controller lama selama controller itu masih memanggil nama view sebelumnya.
- Pisahkan pengelolaan data dari akses publik. Pembacaan awal bisa langsung melalui Blade; endpoint JSON ditambahkan sesuai kebutuhan interaksi, tanpa mengaktifkan route placeholder.
- Input manual VRTT serta target/override disimpan permanen. Draft laporan bulanan yang hanya berada di sesi pengguna tidak otomatis menjadi override dashboard.
- Sumber impor tetap menjadi data dasar. Override menyimpan nilai, sumber, alasan, pencatat, dan waktu; tidak menimpa publikasi/KPI/master secara diam-diam.

Penyimpanan tambahan minimal yang diusulkan:

| Kebutuhan | Dimensi/data utama |
| --- | --- |
| Target indikator | Indikator, tahun, periode tahunan/Q1–Q4, kampus/prodi, kategori, satuan, nilai target, sumber |
| Budget VRTT manual | Tahun, kuartal, prodi, pagu/revisi, realisasi kumulatif, cutoff, bukti/catatan, pencatat |
| Override rekap dan riwayat perubahan | Identitas indikator/periode/prodi, field, nilai, alasan, sumber, pencatat, versi/waktu |
| Sitasi/IP/NTR jika diaktifkan | Sumber terstruktur khusus dengan identitas dan periode yang sesuai indikator; tidak wajib dibuat sebagai tabel kosong sebelum kebutuhannya ditetapkan |

Target/nilai harus bertipe numerik sesuai satuannya. Format lokal seperti `Rp 1.000.000` atau `75 %` diterapkan saat ditampilkan, bukan disimpan sebagai angka berformat.

## Urutan implementasi yang dapat dilakukan

1. Benahi penamaan/view dan siapkan jalur arsip halaman lama ketika route baru diaktifkan.
2. Bangun tampilan baru dengan FM dan mahasiswa dari sumber database, filter tahun/kuartal kumulatif, rincian sesuai kategori, label sumber/cutoff, serta peringatan selisih.
3. Tambahkan input manual VRTT dan target kuartal dengan hak pengelola serta penyimpanan permanen.
4. Hubungkan Research Program setelah satuan perhitungan, atribusi, target, dan skor ditetapkan.
5. Aktifkan sitasi, IP, NTR, dan milestone setelah sumber serta definisinya tersedia. Sampai itu terpenuhi, tampilkan status belum tersedia dan hindari angka contoh.

Sisa keputusan/data yang mempengaruhi implementasi: definisi milestone; target kumulatif per kuartal; populasi persentase FM; sumber resmi untuk selisih rekap mahasiswa dan alokasi target FM; definisi Research Program/NTR; sumber sitasi/IP; arti kartu ringkasan VRTT, cakupan pagu, serta pengguna yang berhak mengelola.

## Verifikasi selama analisis

- Membaca HTML acuan, Blade lama, layout portal, controller/service/route lama dan rancangan remodel, importer, helper skor, serta dokumentasi laporan.
- Menjalankan `php artisan route:list --path=kpi-publikasi` dan `php artisan route:list --path=api/publication-dashboard`.
- Memeriksa struktur tabel, metadata impor, agregat terpilih, dan keberadaan view melalui Laravel/Tinker dengan operasi baca saja. Tidak menjalankan impor atau migrasi.
- Memanggil `PublicationDashboard::data()` untuk memverifikasi cakupan snapshot, proyek, field payload, dan nilai agregat; tidak mengekspor rincian pribadi ke dokumen ini.
- Belum menjalankan pengujian implementasi dashboard atau pemeriksaan visual browser karena belum ada implementasi baru pada tahap analisis ini.

Pengujian saat implementasi perlu memeriksa isolasi tahun/prodi/dosen, pemilihan snapshot kumulatif tanpa penghitungan ganda, selisih rekap, pemisahan nol/kosong, identitas publikasi, perhitungan VRTT, penolakan penulisan tanpa hak, serta tampilan desktop/seluler dan halaman arsip.
