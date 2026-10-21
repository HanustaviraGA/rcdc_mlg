# 🧪 Testing Instructions - Column Mapping Preview v2.0

## Quick Start Testing

### Step 1: Clear Cache
```bash
php artisan view:clear
php artisan cache:clear
```

### Step 2: Open Application
```
http://localhost/backoffice/importrectorate
```

### Step 3: Test Upload Flow

#### Test Case 1: File dengan Missing Required Column ✓

**Setup:**
1. Buka file Excel yang akan diupload
2. Pastikan **ada kolom yang hilang** (misal: hapus kolom "Status")
3. Save file sebagai `test_missing_status.xlsx`

**Steps:**
1. Di aplikasi, pilih Tahun & Bulan
2. Klik input "Publikasi FM (.xlsx)"
3. Select file `test_missing_status.xlsx`
4. **Modal akan muncul dengan ERROR**

**Expected Result:**
```
❌ Gagal membaca file!

┌─ Kolom yang tidak ditemukan: ─────────────┐
│ ✕ Kolom status                           │
│ ✕ Kolom kode_dosen                       │
│ ✕ Kolom request_code                     │
└──────────────────────────────────────────┘

Saran: Pastikan file memiliki kolom-kolom di atas...
```

✅ **PASS:** Jika modal show error dengan missing columns  
❌ **FAIL:** Jika modal tidak ada atau error tidak terlihat

---

#### Test Case 2: File dengan Semua Kolom OK ✓

**Setup:**
1. Siapkan file Excel yang **lengkap semua kolom**
2. Nama kolom standard (Status, Request Code, Kode Dosen, etc)
3. Data tidak kosong

**Steps:**
1. Pilih Tahun & Bulan (contoh: 2025, Oktober)
2. Klik input file
3. Select file lengkap
4. Modal muncul

**Expected Result:**
```
File info: test_complete.xlsx | Sheet: MALANG | 12 kolom ditemukan

Mapping table:
- Request Code    [Wajib] → dropdown menunjukkan [suggested]
- Kode Dosen      [Wajib] → dropdown menunjukkan [suggested]
- Kampus          [Wajib] → dropdown menunjukkan [suggested]
- ...dst

Data Preview:
Tabel data dengan 5 baris pertama

Validation:
✅ Semua kolom wajib berhasil dipetakan
Tombol "Konfirmasi" ENABLED (bisa diklik)
```

✅ **PASS:** Modal show mapping table tanpa error  
❌ **FAIL:** Modal show error padahal semua kolom ada

---

#### Test Case 3: Year-Dependent Column (Status) ✓

**Setup:**
1. File yang di-test untuk tahun 2025
2. Ada kolom "Status" ATAU "St2025"

**Steps:**
1. **PENTING**: Pilih Tahun = **2025**
2. Pilih file yang punya "St2025"
3. Modal muncul

**Expected Result:**
- Modal menerima kolom "St2025" sebagai "Status" yang valid
- Tidak ada error tentang missing status

**Test Variation:**
- Ubah tahun ke 2024
- Upload file yang hanya punya "St2024"
- ERROR: "Kolom status tidak ditemukan" (EXPECTED, karena system cari St2024, file punya St2025)

✅ **PASS:** Year-dependent column matching bekerja  
❌ **FAIL:** Tidak ada perubahan behavior saat tahun berbeda

---

#### Test Case 4: Fuzzy Matching ✓

**Setup:**
1. File dengan kolom nama mirip tapi tidak exact:
   - "Kode Dosen Pengajar" (bukan "Kode Dosen")
   - "Bobot Asli" (bukan "Bobot")
   - "Publikasi" (bukan "Tipe Publikasi")

**Steps:**
1. Upload file tersebut
2. Modal muncul

**Expected Result:**
- Mapping table menunjukkan suggested columns yang "mirip"
- Auto-match dengan fuzzy similarity 60%+
- User bisa accept atau ubah di dropdown

✅ **PASS:** Fuzzy matching suggests reasonable alternatives  
❌ **FAIL:** Exact match hanya, tidak ada suggestion untuk kolom mirip

---

#### Test Case 5: Error Banner Display ✓

**Setup:**
- File dengan multiple missing columns (3+ kolom)

**Steps:**
1. Upload file
2. Lihat error banner yang muncul

**Expected Result:**
```
❌ Gagal membaca file!
┌─ Kolom yang tidak ditemukan: ─┐
│ ✕ Kolom 1                     │
│ ✕ Kolom 2                     │
│ ✕ Kolom 3                     │
│ ✕ Kolom 4                     │
└────────────────────────────────┘

Format:
- Badge dengan "✕" icon
- Warna merah (danger)
- List columns dipisah per baris
- Saran di bawah
```

✅ **PASS:** Semua missing columns terlihat jelas dengan badge  
❌ **FAIL:** Kolom tidak semua terlihat atau format tidak bagus

---

#### Test Case 6: Confirm & Upload Flow ✓

**Setup:**
1. File yang **lengkap & OK**
2. Sudah bisa di-confirm (semua required fields mapped)

**Steps:**
1. Upload file (modal muncul OK)
2. Klik "Konfirmasi dan Lanjutkan Upload"
3. Modal close
4. File input punya badge: "✓ Pemetaan kolom dikonfirmasi"
5. Klik "Upload dan import" di form
6. Upload berhasil

**Expected Result:**
```
File input badge:
✓ Pemetaan kolom dikonfirmasi [Ubah pemetaan]

Success response:
Summary menunjukkan data berhasil diimpor
```

✅ **PASS:** Workflow complete end-to-end  
❌ **FAIL:** Error saat upload setelah confirm

---

## Browser Console Debugging

### Check CSRF Token
```javascript
// Open DevTools (F12) → Console tab
// Paste this:
const token = '{{ csrf_token() }}';
console.log('CSRF Token:', token);
// Should print non-empty token
```

### Check Network Requests
```
F12 → Network tab
Upload file → Look for POST request to /preview-columns

Request:
- file: [file object]
- type: fm_file (or other type)
- year: 2025
- month: 10
- _token: [csrf token]

Response should show:
{
  success: true/false,
  dry_run: {
    success: true/false,
    errors: ["..."],
    missing_columns: ["..."]
  }
}
```

## Test Files

### Create Test File with Missing Column:

```
1. Open Excel / LibreOffice Calc
2. Add headers: Request Code, Kode Dosen, Kampus, (missing Status)
3. Add sample data: 
   - RC001, DEP001, MALANG
   - RC002, DEP002, MALANG
4. Save as: test_missing_status.xlsx
5. Use for Test Case 1
```

### Create Test File Complete:

```
1. Open Excel
2. Add all headers:
   - Request Code
   - Kode Dosen
   - Kampus
   - Submitted
   - Bobot
   - Title
   - Jenis
   - Tipe Publikasi
   - Status (or St2025 if year 2025)
3. Add sample data:
   - RC001, DEP001, MALANG, Scopus FM, 1, Paper Title, Jurnal, Scopus, 1
   - RC002, DEP002, MALANG, Non Scopus FM, 2, Paper Title 2, Seminar, Non Scopus, 1
4. Save as: test_complete.xlsx
5. Use for Test Case 2
```

## Common Issues During Testing

### Issue 1: Modal Doesn't Appear
**Cause:** Bootstrap or JavaScript not loaded
**Debug:**
```javascript
// Check Bootstrap
console.log(typeof bootstrap);
// Should show: object

// Check modal element
console.log(document.getElementById('column-mapping-modal'));
// Should show: HTMLElement, not null
```

**Fix:**
```bash
php artisan cache:clear
php artisan view:clear
# Reload page
```

### Issue 2: "Cannot read properties of null"
**Cause:** CSRF token retrieval issue (OLD VERSION)
**Fix:** Already fixed in this version!
```javascript
const csrfToken = '{{ csrf_token() }}';  // ← Blade injection
```

### Issue 3: Error Banner Shows But Columns Are Blank
**Cause:** Regex parsing didn't match error format
**Debug:**
```javascript
// Check error message format
const msg = "Kolom status tidak ditemukan pada sheet publikasi...";
const regex = /Kolom ([^.]+) tidak ditemukan/gi;
const matches = [];
let m;
while ((m = regex.exec(msg)) !== null) {
    matches.push(m[1]);
}
console.log(matches);  // Should show: ["status"]
```

**Fix:** Adjust regex if error message format changed

### Issue 4: File Upload Still Errors After Modal Confirm
**Cause:** Mapping dari modal tidak disalurkan ke import
**Expected:** Dengan v2.0, error seharusnya tidak muncul lagi di server
**Debug:** Check server logs
```bash
tail -f storage/logs/laravel.log | grep -i "kolom\|error"
```

## Performance Testing

### Test File Size Impact:
- Small file (100KB): <200ms
- Medium file (5MB): <500ms
- Large file (20MB): <1s

**Check:**
```javascript
// F12 → Network tab
// See "preview-columns" request
// Check Duration column
// Should be <1s
```

## Security Testing

### Test CSRF Protection:
```bash
# Try upload without valid CSRF token
curl -X POST http://localhost/backoffice/importrectorate/preview-columns \
  -F "file=@test.xlsx" \
  -F "type=fm_file" \
  -F "_token=invalid_token"

# Should get 419 Expired/Invalid Token error
```

### Test Auth Protection:
```bash
# Try without being logged in
# Should redirect to login page
```

## Regression Testing

### Before Submit to Production, Check:
- [ ] Preview endpoint works (/preview-columns)
- [ ] Error display shows missing columns correctly
- [ ] Fuzzy matching still works
- [ ] CSRF token passed correctly
- [ ] Year/month passed to importer
- [ ] Dry-run doesn't modify database
- [ ] Success case works end-to-end
- [ ] File badges update correctly
- [ ] Modal can be re-opened
- [ ] Console has no JavaScript errors

## Automated Testing

### Run Test Suite:
```bash
php artisan test tests/Feature/ColumnMappingTest.php

# Expected output:
# ✓ fm mapping config has required fields
# ✓ research mapping config has required fields
# ✓ mhs mapping config has required fields
# ✓ suggest mapping exact match
# ✓ suggest mapping partial match
# ✓ validate mapping missing required field
# ✓ validate mapping optional field missing
# ✓ mapping config invalid type
# Tests: 8 passed (0 failed)
```

## Checklist: Ready for Production?

- [ ] All test cases passed
- [ ] No console errors
- [ ] No network errors
- [ ] Error messages display correctly
- [ ] Dry-run doesn't create/modify data
- [ ] CSRF protection working
- [ ] Auth protection working
- [ ] Performance acceptable
- [ ] Database backups available
- [ ] Rollback plan ready

---

**Test Date:** ___________  
**Tested By:** ___________  
**Status:** ☐ PASS ☐ FAIL  
**Notes:** ___________________________________________

---

**Ready to Deploy:** ✅ YES / ❌ NO
