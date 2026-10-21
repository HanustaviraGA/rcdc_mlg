# Fitur Column Mapping Preview

## 📋 Deskripsi Fitur

Fitur Column Mapping Preview memungkinkan pengguna untuk melihat dan mengatur pemetaan kolom antara file Excel yang diupload dengan kolom-kolom yang dibutuhkan oleh sistem sebelum melakukan import data.

Sistem ini dirancang untuk mengatasi perbedaan struktur kolom yang mungkin terjadi antar file, terutama ketika:
- Ada perubahan template file dari waktu ke waktu
- File berasal dari berbagai sumber dengan format berbeda
- Pengguna tidak familiar dengan struktur kolom yang diharapkan sistem

## ✨ Fitur Utama

### 1. **Auto-Detection & Suggestion**
- Sistem secara otomatis membaca header Excel file
- Menggunakan fuzzy matching (Levenshtein similarity) untuk suggest kolom yang sesuai
- Menampilkan akurasi matching dengan badges berwarna

### 2. **Interactive Column Mapping**
- Interface modal yang user-friendly
- Dropdown untuk memilih kolom sumber dari file
- Real-time preview data dengan highlighting kolom yang dipilih
- Validation badge untuk setiap kolom (Required/Optional/Mapped/Unmapped)

### 3. **Data Preview**
- Menampilkan 5 baris pertama dari file sebagai sample
- Highlight kolom yang sudah dipetakan
- Membantu user memverifikasi mapping yang benar

### 4. **Validation & Feedback**
- ✅ Validasi kolom wajib (required fields)
- ⚠️ Warning untuk kolom opsional yang tidak ditemukan
- Mencegah submit jika ada kolom wajib yang belum dipetakan
- Status badge yang jelas di setiap file input

### 5. **Persistent State**
- Mapping yang dikonfirmasi disimpan sampai form disubmit
- Pengguna bisa membuka ulang modal untuk mengubah mapping
- Indikasi visual jika mapping sudah dikonfirmasi

## 🏗️ Struktur Teknis

### Service: `ColumnMapperService`
**Path:** `app/Services/ColumnMapping/ColumnMapperService.php`

Methode utama:
- `preview(string $path): array` - Baca file dan return header + preview data
- `getMappingConfig(string $type): array` - Return konfigurasi kolom per tipe import
- `validateMapping(array $mapping, string $type): array` - Validasi mapping
- `suggestMapping(array $headers, string $type): array` - Suggest mapping otomatis

### Controller: `ColumnMappingController`
**Path:** `Modules/ImportRectorate/app/Http/Controllers/ColumnMappingController.php`

Routes:
- `POST /backoffice/importrectorate/preview-columns` - Preview file & get headers
- `POST /backoffice/importrectorate/validate-mapping` - Validasi mapping configuration
- `GET /backoffice/importrectorate/mapping-config` - Get template config untuk tipe tertentu

### View: `column-mapping.blade.php`
**Path:** `Modules/ImportRectorate/resources/views/column-mapping.blade.php`

Modal yang berisi:
- File info & sheet selector
- Mapping table dengan selects untuk setiap kolom
- Data preview table
- Validation banner
- Action buttons (Confirm/Cancel)

### JavaScript Handler
**Path:** `Modules/ImportRectorate/resources/views/javascript.blade.php`

Handles:
- File input change detection
- Modal opening & data loading
- Mapping table rendering
- Real-time validation & highlighting
- Confirmed mappings state management
- Form submission dengan mapping data

## 🎯 Tipe Import yang Didukung

### 1. **FM_FILE** (Publikasi FM)
**Required Columns:**
- Request Code
- Kode Dosen
- Kampus
- Submitted
- Bobot (Bobot Asli)
- Title
- Jenis
- Tipe Publikasi

**Optional Columns:**
- FM Author
- JJA
- Pendidikan
- Type
- First Author
- Quartile Jurnal
- Publisher
- Tanggal Pelaporan
- Prodi KPI

### 2. **MHS_FILE** (Publikasi Mahasiswa)
**Required Columns:**
- NIM
- Nama Mahasiswa
- Judul
- Tahun

**Optional Columns:**
- Program Studi
- Dosen Pembimbing
- Sumber Publikasi
- Link Publikasi

### 3. **RESEARCH_FILE** (Hibah Penelitian)
**Required Columns:**
- Tahun Anggaran
- Kd. Prop
- Kode Dosen/NIM
- Nama
- Peran
- JUDUL
- Lokasi Kampus

**Optional Columns:**
- NIDN
- Kategori FM/eksternal/Mahasiswa
- Sumber Dana
- Dana Disetujui
- Tanggal Mulai
- Tanggal Selesai
- Program Hibah
- SKEMA
- SDGs

## 🚀 Cara Penggunaan

### Untuk End User:

1. **Masuk ke Import Dashboard**
   ```
   /backoffice/importrectorate
   ```

2. **Pilih Tahun & Bulan Laporan**
   - Set tahun: dropdown atau input manual (2000-2100)
   - Set bulan: dropdown dengan nama bulan (Januari - Desember)

3. **Pilih File Excel**
   - Klik pada input file untuk salah satu tipe (FM, MHS, Research)
   - Sistem akan otomatis membuka modal preview & mapping

4. **Review Auto-Suggested Mapping**
   - Modal menampilkan:
     - Nama file & sheet yang terdeteksi
     - Jumlah kolom
     - Tabel pemetaan dengan suggestion
     - Preview data 5 baris pertama
   - Kolom berwarna menunjukkan status:
     - 🔴 Red badge: Kolom wajib belum dipetakan
     - 🟡 Warning badge: Kolom opsional tidak ditemukan
     - 🟢 Green badge: Kolom sudah dipetakan

5. **Ubah Mapping Jika Diperlukan**
   - Jika suggestion salah, ubah via dropdown di kolom "Kolom File (Sumber)"
   - Real-time preview update dengan highlighting
   - Lihat highlight di tabel data preview di bawah

6. **Validasi Mapping**
   - Periksa validation banner untuk error/warning
   - Tombol "Konfirmasi dan Lanjutkan Upload" aktif ketika semua required field OK
   - Status badge di setiap file input akan berubah menjadi "Pemetaan kolom dikonfirmasi"

7. **Ulangi untuk File Lainnya**
   - Pilih file berikutnya (jika ada)
   - Repeat langkah 3-6

8. **Submit Form**
   - Klik "Upload dan import"
   - Sistem mengirim mapping data bersama file
   - Data diimport dengan mapping yang telah dikonfirmasi

### Untuk Developer (Custom Integration):

#### Use Case: Custom File Import

```php
// Get mapping config
$config = app(\App\Services\ColumnMapping\ColumnMapperService::class)
    ->getMappingConfig('fm_file');

// Preview file
$preview = app(\App\Services\ColumnMapping\ColumnMapperService::class)
    ->preview($path);

// Get suggested mapping
$suggested = app(\App\Services\ColumnMapping\ColumnMapperService::class)
    ->suggestMapping($preview['headers'], 'fm_file');

// Validate mapping
$validation = app(\App\Services\ColumnMapping\ColumnMapperService::class)
    ->validateMapping($userMapping, 'fm_file');
```

#### Use Case: Custom Column Matching

Override `fuzzyMatchHeader()` atau `levenshteinSimilarity()` di ColumnMapperService untuk custom matching logic.

## 📊 Data Flow Diagram

```
User Select File
        ↓
JavaScript triggers file input change
        ↓
Open Column Mapping Modal (Bootstrap)
        ↓
Send file to POST /preview-columns
        ↓
ColumnMappingController.preview()
        ↓
ColumnMapperService.preview() → Read Excel headers & data
        ↓
ColumnMapperService.suggestMapping() → Fuzzy match headers
        ↓
Return JSON: {headers, preview_data, suggested_mapping, config}
        ↓
JavaScript render modal:
  - File info badge
  - Mapping table dengan selects
  - Preview table dengan highlighting
  - Validation banner
        ↓
User adjust mapping (optional)
        ↓
User clicks "Konfirmasi dan Lanjutkan Upload"
        ↓
JavaScript store mapping in confirmedMappings[type]
        ↓
Modal close, badge updated to "Pemetaan dikonfirmasi"
        ↓
[Repeat for other files]
        ↓
User submit form with all files
        ↓
JavaScript check if all files have confirmedMappings
        ↓
Send POST /upload dengan FormData + mapping JSON
        ↓
ImportRectorateController.store() process import
        ↓
Data saved to DB
```

## 🔧 Konfigurasi

### Menambah Tipe Import Baru

Di `ColumnMapperService`:

```php
private function getYourNewTypeMappingConfig(): array
{
    return [
        'required' => [
            'field_name' => 'Display Label',
            // ...
        ],
        'optional' => [
            'field_name' => 'Display Label',
            // ...
        ],
        'aliases' => [
            'field_name' => ['alias1', 'alias2', 'spreadsheet column name'],
            // ...
        ],
    ];
}
```

Update `getMappingConfig()`:
```php
public function getMappingConfig(string $importType): array
{
    return match ($importType) {
        'fm_file' => $this->getFmMappingConfig(),
        'your_type' => $this->getYourNewTypeMappingConfig(),
        // ...
        default => [],
    };
}
```

### Mengubah Threshold Fuzzy Match

Di `fuzzyMatchHeader()`:
```php
// Default: 0.6 (60% similarity)
if ($score > $bestScore && $score >= 0.6) {
    $bestScore = $score;
    $bestIndex = $index;
}
```

## ⚠️ Limitasi & Catatan

1. **Sheet Selection**
   - Sistem mencari sheets dengan nama: MALANG → RAW → DETAIL → DATA
   - Custom sheet names tidak auto-detected, user perlu memilih manual

2. **Header Detection**
   - Hanya baca 1000 baris pertama (default Aspera Reader)
   - Header harus di baris paling atas yang tidak kosong

3. **Data Type Validation**
   - Column Mapper hanya baca & match nama kolom
   - Validation value (format tanggal, numeric, dll) dilakukan di Reader masing-masing importer

4. **Performance**
   - Preview terbatas 5 baris untuk kecepatan
   - File besar (>20MB) tidak bisa diupload
   - Fuzzy matching menggunakan Levenshtein jadi O(n*m) complexity

5. **Browser Support**
   - Memerlukan modern browser dengan FormData, Fetch API, Bootstrap 5
   - IE11 tidak didukung

## 🐛 Troubleshooting

### Modal tidak muncul setelah pilih file
- Pastikan Bootstrap JS sudah di-load
- Cek browser console untuk error JavaScript
- Pastikan csrf-token meta tag ada di page

### Suggestion mapping tidak akurat
- Review file headers - pastikan tidak ada typo/spacing
- Tingkatkan threshold di `levenshteinSimilarity()` jika perlu strict matching
- Ubah aliases di config jika format header berbeda

### File validation gagal saat upload
- Pastikan semua required fields sudah dipetakan
- Cek format file: harus .xlsx, bukan .xls
- Pastikan sheet name match system expectations

### Mapping tidak tersimpan saat form submit
- Pastikan tidak ada JavaScript error saat confirm mapping
- Check form data di browser DevTools Network tab
- Pastikan server menerima `column_mapping` parameter

## 📝 Testing

### Unit Test Contoh

```php
public function test_suggest_mapping_exact_match()
{
    $service = new ColumnMapperService();
    $headers = ['Request Code', 'Kode Dosen', 'Title'];
    $mapping = $service->suggestMapping($headers, 'fm_file');
    
    $this->assertNotEmpty($mapping['request_code']);
    $this->assertNotEmpty($mapping['kode_dosen']);
    $this->assertNotEmpty($mapping['title']);
}

public function test_validate_mapping_missing_required()
{
    $service = new ColumnMapperService();
    $mapping = ['request_code' => 0]; // missing others
    $validation = $service->validateMapping($mapping, 'fm_file');
    
    $this->assertFalse($validation['valid']);
    $this->assertNotEmpty($validation['errors']);
}
```

### Manual Testing Checklist
- [ ] File dengan header match template
- [ ] File dengan header berbeda format/spacing
- [ ] File dengan sheet names berbeda
- [ ] File besar (10MB+)
- [ ] File invalid (tidak ada required columns)
- [ ] Cancel modal tanpa confirm
- [ ] Re-open modal & ubah mapping
- [ ] Submit dengan multiple files
- [ ] Test di mobile/tablet UI

## 📚 Referensi

- [Aspera Spreadsheet XLSX Reader](https://github.com/Aspera/php-spreadsheet-reader)
- [Bootstrap 5 Modal Docs](https://getbootstrap.com/docs/5.0/components/modal/)
- [Levenshtein Distance](https://en.wikipedia.org/wiki/Levenshtein_distance)
- [Laravel Validation](https://laravel.com/docs/10.x/validation)

## 🤝 Contributing

Untuk menambah/improve fitur ini:

1. Fork & create feature branch
2. Update ColumnMapperService jika ada logic baru
3. Update config di getMappingConfig() jika ada kolom baru
4. Update Blade view jika UI berubah
5. Update JavaScript handler jika ada interaksi baru
6. Test thoroughly dengan berbagai file format
7. Update dokumentasi ini
8. Submit PR dengan test evidence

---

**Last Updated:** 21 Oktober 2026
**Version:** 1.0
**Status:** ✅ Stable
