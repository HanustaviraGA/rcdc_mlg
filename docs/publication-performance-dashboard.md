# Dashboard KPI publikasi baru

`/kpi-publikasi` memakai `PublicationPerformanceController`, view `dashboard.publication_kpi`, dan aset `publication-performance.css`/`.js`. Halaman mengikuti susunan HTML Performance Dashboard dengan tema portal dari `publication_kpi_old`: header/footer, tipografi, warna BINUS, dan kartu putih.

`/kpi-publikasi-old` membuka view lama melalui method `legacy`, dengan service `PublicationDashboard` sebelumnya. Controller `PublicationDashboardController`, Blade `_old`, dan aset lama tidak diubah. Route bernama `publication-dashboard` tetap menunjuk `/kpi-publikasi`.

## Filter dan rincian

- Tahun mendukung beberapa pilihan. Kuartal memilih snapshot kumulatif terbaru dalam tahun terpilih yang bulannya tidak melewati batas kuartal. Bulan sumber selalu ditampilkan; snapshot yang belum mencapai akhir kuartal diberi catatan sementara.
- Target tetap diberi label **target tahunan** karena target kuartal belum tersedia. Nilai antar-snapshot bulanan tidak dijumlahkan. Ringkasan beberapa tahun memakai satu snapshot per sumber per tahun, dan menandai tahun yang belum tersedia.
- Program studi mengikuti sumber, dengan urutan kartu sesuai daftar program aplikasi.
- Detail FM menampilkan KPI dosen; klik nama dosen untuk melihat publikasi dalam jurusan/snapshot tersebut. Detail mahasiswa memakai daftar penulis dan judul dari LIST.
- Pencarian, pengurutan kolom, ekspor CSV hasil pencarian, cetak halaman, dan dialog yang dapat ditutup dengan Escape tersedia. CSV memberi perlindungan terhadap teks yang dapat dianggap formula spreadsheet.

## Sumber dan batas cakupan

`PublicationPerformanceDashboard` membaca snapshot FM bulanan dari `rectorate_dosen`, metadata `publication_imports`, dan `publication_kpi_entries`. Snapshot mahasiswa memakai `student_publication_imports`. Payload publik hanya menyertakan field rincian yang diperlukan.

Realisasi FM jurusan/kampus mengikuti metadata Realization Template bila tersedia, dengan bobot asli sebagai fallback. Skor jurusan mengikuti Scoring PI. Rincian bobot dosen memakai MAX(Rectorate, RTTO) melalui helper yang sudah ada; skor dosen mengikuti KPI RTTO dan tidak diganti skor prodi. Nol dan nilai yang belum tersedia dibedakan.

Rekap mahasiswa memakai TITLE, fallback LIST. Selisih dengan Realization Template tetap ditampilkan pada catatan sumber. Persentase FM memakai dosen yang tercatat pada KPI dan publikasi periode itu, bukan klaim populasi FM aktif historis. Persentase beberapa tahun tidak digabungkan.

Milestone, sitasi, Research Program, IP, NTR, dan VRTT mempunyai status belum tersedia sampai rekap/definisi indikatornya siap. Menu Research Gallery tetap menyediakan proyek riset. Halaman ini belum menambahkan editor, penyimpanan budget VRTT manual, atau API penulisan baru. Tidak memakai data contoh atau `localStorage` sebagai sumber capaian.

Tidak ada migrasi atau perubahan data bisnis yang diperlukan untuk tampilan ini.

## Pemeriksaan

- PHP: `php -d extension=pdo_sqlite vendor/bin/phpunit --filter=PublicationDashboardTest`.
- JavaScript: `node --test tests/js/publication-performance.test.mjs`.
- Pemeriksaan browser lokal pada desktop 1440px dan ponsel 390px mencakup sumber data, kuartal kumulatif, filter tahun, rincian FM/MHS, pencarian, pengurutan, unduhan CSV, Escape, keadaan kosong, overflow, dan route lama.
- Artefak pemeriksaan lokal berada di `storage/app/publication-performance-*.png` dan `storage/app/publication-performance-review.json`.
