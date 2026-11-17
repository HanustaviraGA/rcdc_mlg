# Roadmap LRC: Jumlah FM per Prodi

Halaman backoffice `/dashboard/roadmaplrc` (modul `RoadmapLRC`) menampilkan rencana jumlah faculty member (FM) per prodi beserta jenjang pendidikannya (S2 dan S3), serta New FM Recruit (NR) yang masuk setiap tahun, untuk 2026–2035. Bentuk tabelnya mengikuti sheet **JJP** pada workbook `Tambahan data S3B.xlsx`: satu baris per prodi, lima kolom per tahun (Jumlah FM, S2, S3, NR S2, NR S3), dan baris Total per tahun.

## Data

- Tabel `prodi_fm_roadmaps` (migration di `Modules/RoadmapLRC/database/migrations`), satu baris per prodi dan tahun: `prodi`, `program_studi`, `urutan`, `year`, `jumlah_fm`, `jumlah_s2`, `jumlah_s3`, `jumlah_nr_s2`, `jumlah_nr_s3`, serta catatan per angka `catatan_fm`, `catatan_s2`, `catatan_s3`, `catatan_nr_s2`, `catatan_nr_s3`. Kombinasi `prodi` + `year` unik. Kolom NR ditambahkan oleh migration `2026_11_16_000000_add_new_recruits_to_prodi_fm_roadmaps_table`.
- **NR** (New FM Recruit) adalah orang baru dari luar yang masuk menjadi FM pada tahun itu, menurut jenjangnya saat masuk (S2 atau S3). Angkanya per tahun, bukan kumulatif. Dosen yang naik jenjang tidak termasuk NR.
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
- Isi data awal: `php artisan module:seed RoadmapLRC` (`ProdiFmRoadmapSeeder`), yaitu roadmap yang disepakati per 16 November 2026: sheet JJP beserta koreksi yang dibuat di halaman ini, dan NR per tahun. Seeder hanya menambah prodi dan tahun yang belum ada, sehingga angka dan catatan yang sudah diubah di halaman tidak tertimpa.

## Mengubah angka

Setiap angka prodi, termasuk NR, tampil sebagai isian. Klik (atau tekan Enter/spasi pada) isian untuk membuka modal berisi **Nilai** (bilangan bulat 0–9999) dan **Catatan** (opsional, maksimal 1000 karakter; dikosongkan berarti catatan dihapus). Setelah disimpan, tabel dan baris Total dimuat ulang. Mengubah angka harus dalam keadaan login.

Sel yang memiliki catatan diberi segitiga biru di pojok kanan atas; catatannya tampil sebagai tooltip. Bila S2 + S3 tidak sama dengan Jumlah FM, sel Jumlah FM, S2, dan S3 tahun itu ditandai kuning dan halaman menampilkan jumlah isian tersebut (kolom NR tidak ikut dicek). Angka tetap bisa disimpan dalam keadaan ini, sehingga Jumlah FM, S2, dan S3 dapat diubah satu per satu.

## Proyeksi FM S3 di Dashboard LRC

Halaman publik `/dashboard-lrc` menampilkan **Proyeksi FM S3** tepat di bawah chart Jenjang pendidikan dosen: satu kotak per tahun berisi Jml FM S3 / Total FM = persentase (satu angka desimal), dijumlahkan dari baris prodi roadmap di atas. Kotak berderet menyamping dan dapat digeser.

Roadmap dibuat per prodi, sehingga filter Fakultas, Gugus Binaan, dan Program mempersempit proyeksi ke prodi (gugus binaan) dosen yang lolos filter tersebut, misalnya Fakultas SOD → DKV dan DI. Filter Status Dosen tidak memengaruhi proyeksi. Bila tidak ada prodi roadmap yang cocok, halaman menampilkan "Belum ada roadmap untuk prodi pada filter ini."

### Rincian per tahun dan per prodi

Klik kotak tahun untuk membuka rincian tahun tersebut, dihitung oleh `App\Services\LrcDashboard\FmS3Projection`:

```
FM S3 = Eksisting + Proyeksi
Proyeksi = lulus S3 tahun tersebut + NR S3 tahun tersebut
```

- **Data Center** memakai roster dosen saat ini dengan update status pendidikan terbaru (status pendidikan mengalahkan kolom pendidikan). Satu prodi dihitung dari seluruh dosennya, tidak dipersempit filter.
- **Eksisting** tahun pertama adalah dosen S3 saat ini; tahun berikutnya adalah FM S3 tahun sebelumnya (lulusan dan NR S3-nya ikut).
- **Lulus** adalah dosen S2 dan On Going S3 dengan proyeksi lulus S3 di tahun tersebut (proyeksi yang sudah lewat masuk ke tahun pertama).
- **NR S3** diambil dari kolom NR S3 roadmap tahun itu. NR S2 tidak masuk rumus FM S3; NR tetap di jenjang saat masuk.
- Aplikasi tidak menebak NR. Bila proyeksi FM S3 berbeda dari S3 roadmap, atau FM Data Center + NR s/d tahun itu berbeda dari Jumlah FM roadmap, rincian menandai selnya (kuning) dan menyebut prodinya, misalnya DKV yang Data Center-nya 21 FM sementara roadmap 20.

Contoh total seluruh prodi per 16 November 2026: 2027 = 18 + (17 + 1) = 36, 2028 = 36 + (7 + 1) = 44.

Rincian tahun berisi tabel per prodi: Roadmap (FM, S2, S3), Data Center saat ini (FM, S2, On Going S3, S3), FM S3 (Eksisting, Lulus, NR S3, Total), dan jumlah New FM Recruit s/d tahun itu (S2 dan S3, rinciannya di tooltip). Klik prodi untuk melihat daftar FM-nya: nama, kode, pendidikan, status pendidikan, proyeksi lulus S3, dan pendidikan di tahun tersebut. NR roadmap s/d tahun itu ditambahkan sebagai baris **New FM Recruit** beserta jenjang dan tahun masuknya, sehingga jumlah baris = FM Data Center + NR.
