# Upload bulanan dan laporan publikasi

## Penggunaan

1. Buka **ImportRectorate** (`/dashboard/importrectorate`) melalui tombol **Upload 3 sumber bulanan**. Halaman import hibah kini berada di modul ini; alamat lama `/dashboard/import-hibah` mengarah ke sini. Pilih tahun dan bulan laporan.
2. Pilih file publikasi FM, publikasi MHS, dan/atau hibah. File dapat diunggah bertahap. Jika beberapa file dikirim sekaligus dan salah satunya gagal, seluruh perubahan pada pengiriman tersebut dibatalkan.
3. Upload ulang mengganti sumber sejenis pada periode yang sama; sumber lain dan bulan lain tetap tersimpan. Tahun anggaran hibah terpisah dari tahun/bulan laporan.
4. Buka menu **ExportReport** (`/dashboard/exportreport`), pilih periode, lalu **Generate**.
5. Periksa peringatan selisih sumber. Edit judul, periode tampilan, target, realisasi, skor, profil dosen, dan daftar publikasi langsung pada tabel; baris dapat ditambah atau dihapus.
6. Grafik otomatis mengikuti target dan realisasi rekap. Perubahan rincian tidak otomatis mengubah rekap/score karena rekap resmi dan skor RTTO menggunakan sumber berbeda; sesuaikan sel terkait bila diperlukan.
7. **Simpan draft** menyimpan perubahan dalam sesi login dan memulihkannya ketika menu dibuka lagi. **Unduh Word** memvalidasi dan memakai nilai editor saat itu. Override tidak mengubah hasil import atau master dosen. Maksimal lima draft disimpan dalam sesi; Generate ulang membuat draft baru.

## Pemetaan sumber

**PerhitunganKPI** (`/dashboard/perhitungankpi`) langsung menampilkan seluruh dosen hasil import pada semua tahun/bulan. Satu baris mewakili dosen dalam satu bulan laporan; nilai antarbulan tidak dijumlahkan. Pencarian nama/kode/prodi dan filter opsional tahun, bulan, program studi bekerja langsung. Reset mengembalikan semua data. Daftar berasal dari gabungan publikasi, sheet KPI (termasuk dosen bernilai nol atau belum ada di master), dan hibah FM. Hibah dihitung per proyek unik/peran dari seluruh tahun anggaran dalam file bulan tersebut; import hibah lama tanpa bulan laporan tampil terpisah dengan label belum ditentukan. Publikasi mahasiswa tetap digunakan pada laporan MHS, bukan sebagai KPI dosen. Nilai RTTO kosong tetap kosong; file lama tanpa sheet KPI memakai skor sistem yang dihitung hanya dari publikasi bulan terkait.

| Bagian | Sumber |
| --- | --- |
| Publikasi FM | MALANG, fallback Raw; KPI menyimpan RTTO |
| Rekap FM | Realization Template dan rentang Scoring PI; prodi tanpa rekap memakai bobot asli Scopus dari MALANG |
| Rincian KPI dosen | MAX bobot Rectorate/RTTO per kategori; Score KPI RTTO, first author dari MALANG; profil KPI periode terpilih dengan fallback master |
| Publikasi MHS | MALANG, fallback Raw; filter Kampus MALANG, Kode Dosen Mahasiswa, Submitted Scopus/Non Scopus Mahasiswa |
| Rekap Scopus MHS | Blok Scopus pada TITLE, fallback jumlah LIST; target dan rentang skor dari Realization Template/Scoring PI |
| Daftar MHS | LIST; dibandingkan dengan baris Scopus MALANG; bila LIST tidak ada, dibentuk dari MALANG |
| Hibah | MALANG, fallback Detail; Lokasi Kampus BINUS @Malang; termasuk Evidence |
| Master dosen | Malang, fallback FM Malang / FM BINUS Malang; pemetaan nama kolom, tidak berdasarkan urutan |

Generate selalu menggunakan **periode yang dipilih**, tanpa mengambil bulan lain secara diam-diam. Sumber yang belum ada menghasilkan peringatan dan nilai yang belum tersedia dibiarkan kosong. Galeri/dashboard hibah memilih periode laporan terbaru per tahun anggaran, sehingga upload bulan lama tidak menggantikan snapshot terbaru.

Template `resources/reports/publication-template.docx` berasal dari dokumen referensi September. Data personal, angka contoh, dan tautan workbook eksternal sudah dibersihkan. Tabel, ukuran halaman, gaya, dan posisi grafik dipertahankan. Grafik Word dibangun ulang dari override dengan pola kolom bertumpuk: realisasi dan sisa target `MAX(target - realisasi, 0)`, serta label persentase realisasi/target. Program tambahan disertakan dalam bagian FM/MHS yang sesuai.

## Hasil pemeriksaan file contoh

- **Dosen:** 120 baris, 44 kolom, seluruhnya dipetakan. Enam kode baru dibandingkan master lokal yang berisi 114 dosen. Bug `No HP2` diperbaiki ke field `no_hp_2`. Agama, Tax Status, Status Pernikahan, dan NOTE tidak ada pada file baru; nilai lama dipertahankan. Seluruh baris memiliki Campus `Binus Malang`, meskipun Lokasi mencakup 117 MALANG, dua ML001, dan satu SEMARANG; sheet Malang yang sudah diseleksi tetap digunakan.
- **MHS:** MALANG berisi 106 baris (102 Scopus, empat Non Scopus). TITLE Scopus dan LIST sama-sama berjumlah 102: BC 48, CS 29, ILKOM 15, PR 4, DKV 1, DI 5. Realization Template menyebut total 103 dan CS 30. Referensi grafik memakai total 103 namun CS 29 (jumlah rincian 102). Default laporan mengikuti TITLE/LIST (102/29), dengan peringatan dan pilihan override.
- **FM:** rekap resmi 67,9333 dengan target 89 dan skor 2. Metadata target dan rentang skor kini ikut disimpan ketika file FM diupload; import lama tanpa metadata perlu diupload ulang atau diisi manual pada editor.
- **Hibah:** 114 baris peran, 45 proyek, 66 peneliti; 30 ketua dan 84 anggota. Tahun anggaran: 2023 (4 baris), 2024 (9), 2025 (1), 2026 (100). Seluruh kode FM cocok dengan master. Tidak ada nilai nominal/tanggal tidak valid. Data kosong antara lain dana disetujui 10 baris, tanggal mulai/selesai masing-masing dua, SDG satu, program hibah 13, dan skema 11.

## Apakah gap Dashboard KPI terpenuhi?

| Gap/informasi | Kesimpulan |
| --- | --- |
| Peran ketua/anggota, keterlibatan hibah per dosen/prodi, tren per tahun | Terpenuhi oleh identitas peneliti, proposal, tahun anggaran, dan peran. Dashboard sudah menggunakan repository hibah ini. |
| Pengakuan KPI riset, pendanaan, skema/program, tanggal, judul, SDG proyek | Tersedia sebagai sumber dengan sejumlah sel kosong; metadata proyek juga digunakan galeri riset. |
| Cluster faculty member | Dashboard menghitung indikasi A/B/C dari pendidikan/JJA, bukti Scopus FM/RTTO, dan ketua hibah eksternal. Dasar tiap dosen ditampilkan; penetapan tersimpan diprioritaskan. Keanggotaan RIG/Research Center masih perlu dilengkapi. |
| SDG dominan dan rumusan topik | Terisi dari hibah: SDG per proyek unik; topik dari Subtopik Research Roadmap atau judul hibah. Mengikuti filter tahun anggaran dan dosen/prodi. |
| Penetapan mentor/mentee dan rencana resmi 2027 | Tetap memerlukan penetapan/rencana tersendiri. Peta SDG/topik menunjukkan hibah yang telah diupload. |
| Riwayat KPI publikasi 2023–2024 dan skor historis yang disahkan | Belum dipenuhi oleh file hibah; tetap membutuhkan snapshot publikasi/KPI tahun terkait. |

## Validasi

- Perubahan ImportRectorate/PerhitunganKPI diperiksa dengan pengujian endpoint, daftar dosen bernilai nol/belum ada di master, seluruh bulan, pencarian/filter, pemisahan nilai nol dan kosong, hibah tanpa bulan laporan, serta rollback upload. Browser desktop/seluler memeriksa tampilan awal semua data, pencarian, filter, Reset, tiga input upload, dan pembaruan status. Transport pratinjau browser menggunakan fixture; endpoint sebenarnya diuji di SQLite.

- Pengujian PHPUnit untuk import, penggantian periode, prioritas MALANG, rollback, semua kolom dosen, selisih TITLE/LIST, Generate/override/unduh, isolasi draft pengguna, dan integritas XML Word.
- File contoh diproses dalam transaksi yang di-rollback; data contoh tidak dimasukkan permanen.
- Word berhasil membuka dan mengekspor dokumen contoh menjadi PDF; hasil tabel/grafik diperiksa secara visual.
- Editor diperiksa di Chrome pada desktop dan seluler; Generate, perubahan grafik, dan payload simpan diuji. Transport pratinjau browser memakai fixture; endpoint Laravel diuji terpisah dengan PHPUnit.
- Suite keseluruhan memiliki kegagalan lama pada `ExampleTest`: fixture halaman beranda belum menyediakan tabel `database_dosen_new` dalam SQLite.

Untuk lingkungan lain, jalankan `php artisan migrate`. Tidak ada dependensi baru.
