# SDG, topik, dan cluster Dashboard KPI

Dashboard publik `/kpi-publikasi` memakai data dosen, publikasi FM, dan hibah yang telah diimport. Tidak ada migrasi atau import ulang yang diperlukan untuk data yang sudah tersimpan.

## SDG dan topik

- Sumber hibah memakai snapshot laporan terbaru per tahun anggaran melalui `RectorateResearchRepository`. Upload ulang dan banyak anggota tidak menggandakan proyek.
- Proyek diidentifikasi oleh tahun anggaran dan kode proposal. SDG dihitung sekali per proyek per SDG; satu proyek boleh mendukung beberapa SDG. Seluruh SDG yang seri pada jumlah tertinggi ditampilkan.
- Filter tahun memakai tahun anggaran. Filter dosen/prodi/cluster menentukan proyek melalui keanggotaan FM. Proyek bersama dihitung satu kali dalam cakupan, sementara topiknya dapat muncul pada tiap dosen yang terlibat.
- Rumusan topik memakai `Subtopik Research Roadmap`; jika kosong, memakai judul hibah. Judul, kode proposal, tahun, dan sumber rumusan tetap ditampilkan. Keyword SDG dapat dibuka dalam rincian; tidak dipakai untuk menebak nomor SDG yang kosong.
- Tombol SDG dominan dan pilihan SDG mempersempit tabel topik. Pencarian mencakup nama, kode dosen, prodi, topik, judul, keyword, dan SDG.
- Tahun yang hanya memiliki hibah tetap tersedia tanpa mengisi skor publikasi yang tidak ada. Rencana 2027 yang tersimpan tidak dihapus; bagian ini sekarang menampilkan peta riset dari hibah.

## Cluster

Kriteria mengikuti pengelompokan yang sudah tercantum pada dashboard. Nilai otomatis adalah indikasi berdasarkan cakupan data yang tersedia.

| Cluster | Dasar otomatis |
| --- | --- |
| A | S3 atau JJA L/LK/GB, ada Scopus, dan ada peran ketua hibah eksternal |
| B | S3 atau JJA L/LK/GB, ada Scopus, ketua hibah eksternal belum tercatat |
| C | Kriteria A/B belum terpenuhi pada data import; keanggotaan RIG/Research Center ditampilkan jika tersedia dan ditandai perlu dilengkapi bila kosong |
| Tidak tercatat | Pendidikan dan JJA tidak tersedia, atau profil memenuhi syarat tetapi data publikasi belum tersedia sampai tahun tersebut |

Profil memakai master terbaru, dengan fallback ke profil publikasi/KPI. Bukti Scopus memakai judul Scopus FM atau bobot Scopus RTTO positif, dari riwayat sampai tahun terpilih. Hibah juga dibatasi sampai tahun tersebut. Angka judul tidak menggandakan RequestCode yang muncul di beberapa tahun.

Pemberi hibah/institusi BINUS, HIBUS, internal, mandiri, atau pribadi dihitung internal. Sumber nasional/internasional atau institusi eksternal yang eksplisit menjadi bukti eksternal. `Internasional - PIB` dengan pemberi BINUS tetap internal. Sumber dana yang tidak dikenali ditandai belum dapat diklasifikasikan. Huruf A/B/C pada Faculty Type bukan input cluster.

Penetapan dalam `kpi_fm_profiles` diprioritaskan; hasil perhitungan dan bukti tetap tersedia untuk pemeriksaan. Label mentor tetap mengikuti penetapan yang tersimpan.

## Pemeriksaan data lokal 25 September 2026

- 45 proyek hibah pada 2023–2026.
- Tahun 2026: 35 proyek unik; SDG 8 dan 11 masing-masing 15 proyek, SDG 4 sebanyak 14, SDG 9 sebanyak 10.
- Indikasi cluster 2026: A 2, B 47, C 71; 3 dosen belum cukup data. Angka mengikuti master dan import lokal saat pemeriksaan.
- Pengujian PHP memeriksa penggabungan anggota, pemisahan tahun, sumber terbaru, privasi kolom, cluster, PIB internal, Scopus RTTO, dan penetapan manual.
- Pengujian JavaScript memeriksa hitungan proyek unik, hasil seri, filter dosen/tahun, dan hasil kosong.
- Browser memakai endpoint Laravel lokal sebenarnya: grafik, tombol SDG, pencarian topik, Reset, filter cluster/prodi/tahun, serta tampilan desktop dan seluler.
