# 🔧 Error Handling Guide - Column Mapping Preview

## Overview

Sistem sekarang **mendeteksi error kolom di modal preview** (sebelum upload), bukan di saat upload. Ini membantu Anda mengidentifikasi masalah **lebih cepat**.

## Cara Error Ditampilkan

### 1. Error Banner
```
❌ Gagal membaca file!
┌─ Kolom yang tidak ditemukan: ─┐
│ ✕ Kolom request_code           │
│ ✕ Kolom status                 │
│ ✕ Kolom kampus                 │
└─────────────────────────────────┘

Saran: Pastikan file memiliki kolom-kolom di atas dengan nama 
       yang sesuai atau sesuai dengan alias yang dikenali sistem.
```

### 2. Tipe Error yang Ditampilkan

#### Error: "Kolom xyz tidak ditemukan"
**Penyebab:** Kolom wajib tidak ada di file atau nama berbeda
**Solusi:** 
- Pastikan spreadsheet punya kolom dengan nama yang sesuai
- Gunakan nama kolom standar (lihat daftar di bawah)
- Atau rename kolom di file Excel

#### Error: "Header xyz pada sheet KPI harus ada tepat satu kali"
**Penyebab:** Sheet KPI invalid (duplikat kolom atau tidak ada)
**Solusi:**
- Pastikan sheet KPI ada (jika diperlukan)
- Periksa tidak ada kolom duplikat
- Jika tidak ada KPI, hapus sheet KPI

#### Error: "Sheet MALANG atau Raw tidak ditemukan"
**Penyebab:** File tidak punya sheet dengan nama yang diharapkan
**Solusi:**
- Rename sheet menjadi "MALANG" atau "RAW" (case-insensitive)
- Atau gunakan nama sheet default: DETAIL, DATA

#### Error: "Baris X: [field] kosong"
**Penyebab:** Ada baris data dengan field wajib kosong
**Solusi:**
- Isi nilai kosong di Excel
- Atau hapus baris kosong

## Kolom yang Dikenali Sistem

### FM Publication (`fm_file`)

**Kolom Wajib:**
```
✓ Request Code     (Alias: requestcode, request code)
✓ Kode Dosen       (Alias: kode dosen, kode_dosen, lecturer code)
✓ Kampus           (Alias: kampus, campus)
✓ Submitted        (Alias: submitted, tipe laporan)
✓ Bobot            (Alias: bobot, bobot asli, bobot kontribusi)
✓ Title            (Alias: title, judul)
✓ Jenis            (Alias: jenis, tipe)
✓ Tipe Publikasi   (Alias: tipe publikasi, publikasi, tipe)
✓ Status           (Alias: status, st2025, st{tahun})
```

**Kolom Opsional:**
```
- FM Author        (Alias: fm author, fm_author)
- JJA              (Alias: jja)
- Pendidikan       (Alias: pendidikan)
- Type             (Alias: type)
- First Author     (Alias: first author, first_author)
- Quartile Jurnal  (Alias: quartile, quartile jurnal, q)
- Publisher        (Alias: publisher, penerbit)
- Tanggal Pelaporan (Alias: tanggal, date, tgl)
- Prodi KPI        (Alias: prodi, prodi kpi)
```

### Student Publication (`mhs_file`)

**Kolom Wajib:**
```
✓ NIM              (Alias: nim, nomor induk mahasiswa)
✓ Nama Mahasiswa   (Alias: nama, nama mahasiswa, nama_mhs)
✓ Judul            (Alias: judul, title, nama karya)
✓ Tahun            (Alias: tahun, year, thn)
✓ Kampus           (Alias: kampus, campus)
✓ Submitted        (Alias: submitted, tipe laporan)
✓ Tipe Publikasi   (Alias: tipe publikasi, publikasi, tipe)
```

**Kolom Opsional:**
```
- Program Studi    (Alias: prodi, program studi)
- Dosen Pembimbing (Alias: dosen, pembimbing, dosen pembimbing)
- Sumber Publikasi (Alias: sumber, publikasi, tempat publikasi)
- Link Publikasi   (Alias: link, url, website)
```

### Research Grant (`research_file`)

**Kolom Wajib:**
```
✓ Tahun Anggaran      (Alias: tahun anggaran, tahun, budget year)
✓ Kd. Prop            (Alias: kd prop, kd. prop, kode prop)
✓ Kode Dosen/NIM      (Alias: kode dosen/nim, kode dosen, kode)
✓ Nama                (Alias: nama, nama peneliti, name)
✓ Peran               (Alias: peran, role, posisi)
✓ JUDUL               (Alias: judul, title)
✓ Lokasi Kampus       (Alias: lokasi kampus, kampus, location)
```

**Kolom Opsional:**
```
- NIDN                     (Alias: nidn, nip)
- Kategori FM/eksternal    (Alias: kategori, category)
- Sumber Dana              (Alias: sumber dana, sumber, funding)
- Dana Disetujui           (Alias: dana disetujui, dana, approved amount)
- Tanggal Mulai            (Alias: tanggal mulai, start date, dari)
- Tanggal Selesai          (Alias: tanggal selesai, end date, sampai)
- Program Hibah            (Alias: program hibah, program, scheme)
- SKEMA                    (Alias: skema, scheme, tipe hibah)
- SDGs                     (Alias: sdgs, sustainable development goals)
```

## Common Issues & Solutions

### Issue 1: "Kolom Status tidak ditemukan"
**Status Column Requirement:**
- Nama HARUS: `Status` ATAU `St` + tahun (misal: `St2025`)
- Tahun diambil dari form upload Anda
- Case-insensitive (STATUS, status, Status semua OK)

**Contoh:**
- ✅ Nama kolom: "Status" → OK
- ✅ Nama kolom: "St2025" (tahun 2025) → OK
- ✅ Nama kolom: "STATUS" → OK
- ❌ Nama kolom: "Stat" → ERROR
- ❌ Nama kolom: "St" → ERROR (incomplete)

**Solusi:**
```
Buka Excel:
1. Cari kolom yang nama-nya mirip "Status"
2. Rename menjadi "Status" ATAU "St" + tahun (contoh: St2025)
3. Pastikan semua cells di kolom itu punya nilai (tidak kosong)
```

### Issue 2: "Sheet MALANG atau Raw tidak ditemukan"
**Sistem mencari sheets dalam urutan:**
1. MALANG (preferred)
2. RAW
3. DETAIL
4. DATA

**Solusi:**
```
Buka Excel → klik kanan tab sheet:
- Rename sheet menjadi "MALANG" atau "RAW"
- ATAU gunakan salah satu nama di atas
- Case-insensitive (malang, MALANG, Malang semuanya OK)
```

### Issue 3: "Kolom Request Code tidak ditemukan"
**Sistem accept:**
- "Request Code" (spasi, exact)
- "requestcode" (lowercase, no space)
- "request code" (lowercase, spasi)

**Solusi:**
```
Rename di Excel menjadi salah satu nama di atas.
Atau gunakan nama lain dari alias list.

Contoh:
✅ "Request Code"
✅ "requestcode"
✅ "request code"
❌ "ReqCode" (alias tidak diterima)
❌ "Req.Code" (format berbeda)
```

### Issue 4: "Baris 5: Kode Dosen kosong"
**Penyebab:** Ada baris data dengan cell kosong

**Solusi:**
```
Buka Excel → lihat baris 5:
1. Isi cell yang kosong
2. ATAU hapus baris kalau tidak perlu
3. Save file
4. Upload ulang
```

## Fuzzy Matching

Sistem menggunakan **Levenshtein Distance** untuk match kolom yang mirip (tapi tidak exact):

```
Contoh:
"Kode Dosen Pengajar" ≈ "Kode Dosen" 
  Similarity: 85% → MATCH ✓

"Bobot Asli" ≈ "Bobot"
  Similarity: 67% → MATCH ✓

"Kd" ≈ "Kode Dosen"
  Similarity: 20% → NO MATCH ✗
```

**Threshold:** 60% (jika < 60%, tidak match)

## Workflow Perbaikan

```
1. Upload file → Modal muncul
                    ↓
2. Lihat error di modal "Kolom yang tidak ditemukan"
                    ↓
3. Tutup modal (X button)
                    ↓
4. Buka file Excel di laptop/PC
                    ↓
5. Cari kolom yang error → Rename sesuai saran
                    ↓
6. Save file
                    ↓
7. Klik file input lagi → Modal muncul
                    ↓
8. Kalau sudah OK → Klik "Konfirmasi"
                    ↓
9. Upload dan import
```

## Testing Your File

### Sebelum Upload:
1. Buka file di Excel/LibreOffice
2. Periksa sheet names → harus MALANG/RAW/DETAIL/DATA
3. Periksa headers → harus sesuai alias list
4. Periksa data → tidak ada cell kosong di kolom wajib

### Setelah Upload ke Modal:
1. Lihat preview data di bawah modal
2. Lihat mapping table → apakah mapping suggestions OK?
3. Jika ada ⚠️ warning → optional column tidak ditemukan (OK, data akan kosong)
4. Jika ada ❌ error → required column tidak ditemukan (ERROR, harus fix)

## Getting Help

### Error Message Reference

| Error | Meaning | Fix |
|-------|---------|-----|
| "Kolom X tidak ditemukan" | Required column missing | Rename/add column di Excel |
| "Sheet X tidak ditemukan" | Wrong sheet name | Rename sheet |
| "Baris N: [field] kosong" | Empty cell | Fill value or delete row |
| "Kolom X ambigu atau duplikat" | Column name appears 2+ times | Rename or delete duplicate |
| "Tidak ada baris dengan [criteria]" | No data matching filter | Check data or filter criteria |

### Debug Steps

```
Jika masih error:

1. Check browser console (F12)
   - Lihat network tab
   - Request ke /preview-columns
   - Response punya 'errors' atau 'missing_columns'?

2. Check server logs
   - storage/logs/laravel.log
   - Cari error message yang detail

3. Re-check file
   - Sheet name correct?
   - Column names exact match?
   - Data not empty?
   - Format valid Excel (.xlsx)?

4. Clear cache
   php artisan cache:clear
   php artisan view:clear
```

## Prevention Tips

### ✅ Best Practices
- Use standard column names from alias list
- Keep sheet names simple (MALANG, RAW, etc)
- Avoid special characters in column names
- Fill all required fields (no empty cells)
- Test small file first before bulk
- Keep file format as .xlsx (not .xls)
- Check one file successfully before uploading multiple

### ❌ Common Mistakes
- Typos in column names
- Using translations (Indonesia + English mixed)
- Missing required columns
- Empty data cells
- Wrong sheet names
- Using .xls format instead of .xlsx
- Merged cells
- Formula results not saved (convert to value)

---

**Last Updated:** 21 October 2026  
**Version:** 2.0  
**Status:** Enhanced with dry-run validation
