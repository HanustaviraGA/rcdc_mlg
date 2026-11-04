# Character Building (CBDC)

Modules/CharacterBuilding (`/dashboard/characterbuilding`) memiliki lima tab:

1. **Target Skor**: setiap periode memiliki satu target dan skornya. Publikasi, Community Development, dan
   Pengembangan Diri memakai tahun dan kuartal (Q1–Q4); Pengajaran memakai tahun akademik dan
   semester (Ganjil/Genap), seperti ImportPKM. Target dalam satu tahun (keempat kuartal, atau kedua
   semester pada satu tahun akademik) membentuk **rentang skor** tahun itu. Realisasi mendapat skor
   dari target tertinggi yang dicapainya. Contoh Publikasi 2026: Q1 3 judul → skor 2, Q2 6 → 3,
   Q3 9 → 4, Q4 11 → 6, sehingga rentangnya 3–5 → 2, 6–8 → 3, 9–10 → 4, dan ≥ 11 → 6 (maksimal);
   realisasi 7 atau 8 judul mendapat skor 3, dan di bawah 3 belum mendapat skor. Rentang yang sama
   berlaku untuk kuartal mana pun pada tahun itu. Dalam satu tahun, target yang lebih tinggi harus
   memiliki skor yang lebih tinggi, dan target yang sama harus memiliki skor yang sama. Target
   Publikasi berupa jumlah judul Scopus dan target Community Development berupa jumlah kegiatan. Skor Publikasi dan
   Community Development berskala 0–6; Pengajaran dan Pengembangan Diri 0–100. Menyimpan periode yang sudah
   memiliki target akan menggantinya; tombol **Ubah** dapat memindahkan target ke periode lain yang
   belum memiliki target.
2. **Publikasi**: realisasi CBDC adalah jumlah judul Scopus (Tipe Publikasi Scopus dan Submitted
   Scopus FM) dosen CBDC pada upload Publikasi FM (Import Rectorate) terakhir di kuartal terpilih.
   Judul yang ditulis beberapa dosen CBDC dihitung sekali. File FM bersifat kumulatif dalam satu
   tahun, sehingga upload terakhir menunjukkan posisi akhir kuartal. Dosen CBDC adalah dosen dengan
   prodi CBDC menurut perhitungan KPI (Jurusan Binaan sheet KPI, lalu Gugus Binaan Data Center).
   Tabel dosen juga menampilkan judul Scopus, bobot, dan skor KPI masing-masing sebagai informasi.
3. **Pengajaran**: coming soon.
4. **Community Development**: realisasi CBDC adalah jumlah kegiatan pada baris CBDC sheet Rasio (ImportPKM); bila
   baris itu tidak memuat jumlah kegiatan, dipakai jumlah kegiatan yang dilaporkan dosen CBDC pada
   sheet Keterlibatan. Community Development diunggah per semester, sehingga kuartal memakai semester yang
   mencakup sebagian besar bulannya (Ganjil September–Februari, Genap Maret–Agustus): Q1 tahun Y
   memakai Ganjil (Y-1)/Y, Q2 dan Q3 memakai Genap (Y-1)/Y, Q4 memakai Ganjil Y/(Y+1). Dosen CBDC
   adalah dosen dengan Gugus Binaan Character Building, sama seperti Dashboard ComDev.
5. **Pengembangan Diri**: coming soon.

Tab Publikasi dan Community Development menampilkan realisasi CBDC, target kuartal terpilih beserta status
tercapai, dan skor yang diperoleh (dengan kekurangan menuju skor berikutnya).

Halaman publik **Dashboard CBDC** (`/dashboard-cbdc`, menu Dashboard di header) menampilkan data
yang sama untuk satu kuartal: realisasi, target, dan skor Publikasi dan Community Development
(Pengajaran dan Pengembangan Diri coming soon), perkembangan Q1–Q4 pada tahun tersebut, serta
rincian dosen CBDC dalam empat kartu dengan urutan yang sama:

- **Publikasi** mengikuti Dashboard Riset: tombol **Detail dosen** membuka daftar dosen CBDC (JJA,
  faculty type, bobot Non Scopus/Scopus, first author, skor KPI) yang dapat dicari, diurutkan, dan
  diekspor ke CSV; klik nama dosen untuk melihat ringkasan dan tabel publikasinya beserta filter
  publikasi. Dosen yang hanya tercatat pada data hibah tidak ditampilkan, sama seperti Dashboard Riset.
- **Pengajaran** dan **Pengembangan Diri**: coming soon.
- **Community Development** mengikuti Dashboard ComDev: tombol **List dosen** membuka daftar dosen
  CBDC dengan jumlah kegiatan, skor KPI, dan status, diurutkan dari skor terendah.

Tanpa filter, halaman memakai kuartal terakhir yang memiliki upload Publikasi FM. Tabel publikasi
dan filternya memakai kode yang sama dengan Dashboard Riset (`public/js/publication-performance.js`).

Jalankan migrasi modul `2026_11_04_000000_create_character_building_targets_table.php`
(`php artisan migrate`). Menu sidebar memerlukan baris `sys_menu` dengan `menu_kode`
`characterbuilding` di bawah menu induk Character Building, beserta `sys_role_menu`-nya.
