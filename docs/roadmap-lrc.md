# Roadmap LRC: Jumlah FM per Prodi

Halaman backoffice `/dashboard/roadmaplrc` (modul `RoadmapLRC`) menampilkan rencana jumlah faculty member (FM) per prodi beserta jenjang pendidikannya (S2 dan S3) untuk 2026–2035. Bentuk tabelnya mengikuti sheet **JJP** pada workbook `Tambahan data S3B.xlsx`: satu baris per prodi, tiga kolom per tahun (Jumlah FM, S2, S3), dan baris Total per tahun.

## Data

- Tabel `prodi_fm_roadmaps` (migration di `Modules/RoadmapLRC/database/migrations`), satu baris per prodi dan tahun: `prodi`, `program_studi`, `urutan`, `year`, `jumlah_fm`, `jumlah_s2`, `jumlah_s3`, serta catatan per angka `catatan_fm`, `catatan_s2`, `catatan_s3`. Kombinasi `prodi` + `year` unik.
- `prodi` memakai kode di sheet. `program_studi` adalah nama prodi yang sama di Data Center (`nama_gugus_binaan`):

  | Kode | Program studi |
  | --- | --- |
  | EBC | Entrepreneurship |
  | CS | Computer Science |
  | DP | Psychology |
  | DKV | Visual Communication Design |
  | DI | Interior Design |
  | PR | Public Relations |
  | COMM | Communications |
  | CBDC | Character Building |
  | ENG | English Literature |

- Baris Total dihitung dari baris prodi, tidak disimpan.
- Isi data awal: `php artisan module:seed RoadmapLRC` (`ProdiFmRoadmapSeeder`). Seeder hanya menambah prodi dan tahun yang belum ada, sehingga angka dan catatan yang sudah diubah di halaman tidak tertimpa.

## Mengubah angka

Setiap angka prodi tampil sebagai isian. Klik (atau tekan Enter/spasi pada) isian untuk membuka modal berisi **Nilai** (bilangan bulat 0–9999) dan **Catatan** (opsional, maksimal 1000 karakter; dikosongkan berarti catatan dihapus). Setelah disimpan, tabel dan baris Total dimuat ulang. Mengubah angka harus dalam keadaan login.

Sel yang memiliki catatan diberi segitiga biru di pojok kanan atas; catatannya tampil sebagai tooltip. Bila S2 + S3 tidak sama dengan Jumlah FM, ketiga sel tahun itu ditandai kuning dan halaman menampilkan jumlah isian tersebut. Angka tetap bisa disimpan dalam keadaan ini, sehingga Jumlah FM, S2, dan S3 dapat diubah satu per satu.

## Koreksi dari sheet

Pada sheet ada 7 isian yang S2 + S3-nya tidak sama dengan Jumlah FM. Seeder mempertahankan Jumlah FM dan menyesuaikan S2 atau S3 mengikuti tren tahun di sekitarnya. Angka asli sheet disimpan di catatan sel yang dikoreksi.

| Prodi | Tahun | Jumlah FM | Sheet | Seeder |
| --- | --- | --- | --- | --- |
| CS | 2026 | 21 | S3 4 | S3 3 |
| CBDC | 2031 | 11 | S2 5 | S2 6 |
| EBC | 2033 | 40 | S3 39 | S3 38 |
| EBC | 2034 | 41 | S3 40 | S3 41 |
| EBC | 2035 | 42 | S3 41 | S3 42 |
| ENG | 2034 | 2 | S2 2 | S2 0 |
| ENG | 2035 | 2 | S2 2 | S2 0 |

## Proyeksi FM S3 di Dashboard LRC

Halaman publik `/dashboard-lrc` menampilkan **Proyeksi FM S3** tepat di bawah chart Jenjang pendidikan dosen: satu kotak per tahun berisi Jml FM S3 / Total FM = persentase (satu angka desimal), dijumlahkan dari baris prodi roadmap di atas. Kotak berderet menyamping dan dapat digeser.

Roadmap dibuat per prodi, sehingga filter Fakultas, Gugus Binaan, dan Program mempersempit proyeksi ke prodi (gugus binaan) dosen yang lolos filter tersebut, misalnya Fakultas SOD → DKV dan DI. Filter Status Dosen tidak memengaruhi proyeksi. Bila tidak ada prodi roadmap yang cocok, halaman menampilkan "Belum ada roadmap untuk prodi pada filter ini."
