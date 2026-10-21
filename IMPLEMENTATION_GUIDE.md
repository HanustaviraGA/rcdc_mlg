# Implementation Guide: Column Mapping Preview

## 🚀 Quick Start

### 1. Verify Installation

Pastikan semua file sudah tersimpan:

```bash
# Service
ls app/Services/ColumnMapping/ColumnMapperService.php

# Controller
ls Modules/ImportRectorate/app/Http/Controllers/ColumnMappingController.php

# View
ls Modules/ImportRectorate/resources/views/column-mapping.blade.php

# Updated files
ls Modules/ImportRectorate/resources/views/javascript.blade.php
ls Modules/ImportRectorate/resources/views/index.blade.php
ls Modules/ImportRectorate/routes/web.php

# Tests
ls tests/Feature/ColumnMappingTest.php
```

### 2. Run Tests

```bash
# Test column mapper service
php artisan test tests/Feature/ColumnMappingTest.php

# Or dengan verbose output
php artisan test tests/Feature/ColumnMappingTest.php -v
```

### 3. Clear Cache

```bash
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 4. Test di Browser

1. Buka: `http://localhost/backoffice/importrectorate`
2. Pilih tahun dan bulan
3. Pilih file Excel untuk salah satu tipe (FM, MHS, atau Research)
4. Verifikasi modal muncul dengan:
   - File info & sheet detection
   - Mapping table dengan suggested columns
   - Data preview
   - Status validation
5. Ubah mapping jika diperlukan
6. Klik "Konfirmasi dan Lanjutkan Upload"
7. Ulangi untuk file lainnya
8. Submit form

## 📋 File Checklist

### Backend Files Created/Modified

✅ **New Service**
- `app/Services/ColumnMapping/ColumnMapperService.php`
  - 300+ lines
  - Methods: preview, getMappingConfig, validateMapping, suggestMapping
  - Config untuk FM, MHS, Research files
  - Fuzzy matching logic

✅ **New Controller**
- `Modules/ImportRectorate/app/Http/Controllers/ColumnMappingController.php`
  - 3 endpoints: preview-columns, validate-mapping, mapping-config
  - Auth checks & validation

✅ **Routes Updated**
- `Modules/ImportRectorate/routes/web.php`
  - Added 3 new routes

### Frontend Files Created/Modified

✅ **New View**
- `Modules/ImportRectorate/resources/views/column-mapping.blade.php`
  - Modal component
  - 150+ lines
  - Responsive design with Bootstrap 5

✅ **Views Updated**
- `Modules/ImportRectorate/resources/views/index.blade.php`
  - Added modal inclusion
  - Updated labels dengan info badges
  - Improved description

✅ **JavaScript Updated**
- `Modules/ImportRectorate/resources/views/javascript.blade.php`
  - Complete rewrite
  - 300+ lines
  - All interactive logic
  - State management for mappings

### Test Files

✅ **New Test File**
- `tests/Feature/ColumnMappingTest.php`
  - 10 test cases
  - Coverage untuk semua mapping types
  - Validation tests

### Documentation

✅ **Documentation Files**
- `COLUMN_MAPPING_PREVIEW.md` (comprehensive guide)
- `IMPLEMENTATION_GUIDE.md` (this file)

## 🔍 Key Implementation Details

### 1. Column Detection Strategy

**Priority Order:**
1. Exact match dengan aliases (case-insensitive)
2. Fuzzy match dengan Levenshtein similarity (threshold 0.6)
3. Manual selection via dropdown

**Example:**
```php
// Header: "Kode Dosen Pengajar"
// Will match: "kode_dosen" (fuzzy ~ 0.85 similarity)

// Header: "something_random"
// Will NOT match (similarity ~ 0.15 < threshold 0.6)
```

### 2. Validation Logic

**Required Fields Validation:**
```php
if (empty($mapping['field'])) {
    $errors[$field] = "Kolom '{$label}' wajib dipetakan";
}
```

**Optional Fields Warning:**
```php
if (empty($mapping['field'])) {
    $warnings[$field] = "Kolom '{$label}' tidak ditemukan - data akan kosong";
}
```

### 3. State Management

**In JavaScript:**
```javascript
const confirmedMappings = {};  // {type: {field: index, ...}}

// User select file → open modal
// User confirm mapping → store in confirmedMappings[type]
// User submit form → send confirmedMappings dengan FormData
```

### 4. Data Flow

**File Select:**
```
User clicks file input
  ↓ (onChange event)
Trigger openMappingModal()
  ↓
POST /preview-columns dengan file
  ↓
ColumnMapperService.preview() membaca file
  ↓
Return headers + preview data + suggested mapping
  ↓
JavaScript render modal UI
```

**Confirm Mapping:**
```
User adjust mapping di dropdowns
  ↓
Real-time validation + highlighting
  ↓
User click "Konfirmasi"
  ↓
Store mapping in confirmedMappings[type]
  ↓
Update file input badge
  ↓
Modal close
```

**Submit Form:**
```
User click "Upload dan import"
  ↓
JavaScript check: all selected files have confirmedMappings
  ↓
Add column_mapping[type] to FormData
  ↓
POST /upload dengan files + mappings
  ↓
ImportRectorateController.store() process
  ↓
Data saved to DB
```

## 🎨 UI/UX Highlights

### Modal Layout
```
┌─ Header ─────────────────────────────────────────┐
│ Pemetaan Kolom                                   │ X
│ Sesuaikan kolom file dengan kolom sistem        │
├───────────────────────────────────────────────────┤
│ [INFO BAR: File, Sheet, Column Count]           │
│                                                   │
│ [VALIDATION BANNER: Errors/Warnings/Success]    │
│                                                   │
│ Pemetaan Kolom Sistem → Kolom File              │
│ ┌─────────────────────────────────────────────┐  │
│ │ Kolom Sistem│Type│Kolom File│Contoh│Status│  │
│ ├─────────────────────────────────────────────┤  │
│ │ Request Code│Req │[select] ↓│...   │  ✓   │  │
│ │ Kode Dosen  │Req │[select] ↓│...   │  ✓   │  │
│ │ ... (more rows)                           │  │
│ └─────────────────────────────────────────────┘  │
│                                                   │
│ Preview Data File (5 baris pertama)            │
│ ┌─────────────────────────────────────────────┐  │
│ │ [Highlighted columns dengan warna]         │  │
│ └─────────────────────────────────────────────┘  │
├───────────────────────────────────────────────────┤
│ [Batal] [⚠️ Periksa mapping] [✓ Konfirmasi] │
└───────────────────────────────────────────────────┘
```

### Status Badges

- 🔴 **Wajib** (Red): Required field not mapped
- 🟡 **Opsional** (Yellow): Optional field not found
- 🟢 **Terpetakan** (Green): Field successfully mapped
- ⚪ **Tidak Dipetakan** (Gray): Deliberately unmapped

### File Input Badge

After modal confirm:
```
✓ Pemetaan kolom dikonfirmasi [Ubah pemetaan]
```

Error state:
```
✗ Gagal membaca file. Coba file lain.
```

## 🔧 Configuration Reference

### Add New Import Type

**Step 1:** Add config method in ColumnMapperService

```php
private function getNewTypeMappingConfig(): array
{
    return [
        'required' => ['field' => 'Display Label', ...],
        'optional' => ['field' => 'Display Label', ...],
        'aliases' => ['field' => ['alias1', 'alias2'], ...],
    ];
}
```

**Step 2:** Update getMappingConfig()

```php
public function getMappingConfig(string $importType): array
{
    return match ($importType) {
        'new_type' => $this->getNewTypeMappingConfig(),
        default => [],
    };
}
```

**Step 3:** Update Controller validation

```php
$validated = $request->validate([
    'type' => ['required', 'in:fm_file,mhs_file,research_file,new_type'],
    // ...
]);
```

**Step 4:** Add to ImportRectorateController if needed

```php
foreach (['fm_file', 'mhs_file', 'research_file', 'new_type'] as $type) {
    if (!$request->hasFile($type)) continue;
    // ... handle new type
}
```

### Customize Fuzzy Matching

In ColumnMapperService:

```php
// Increase threshold untuk strict matching
if ($score > $bestScore && $score >= 0.8) {  // was 0.6

// Or implement custom matching for specific fields
private function matchHeaderStrict(string $target, array $headers): ?int
{
    // Custom logic here
}
```

### Modify Aliases

Example: Add support untuk bahasa Indonesia/Inggris mix

```php
'aliases' => [
    'request_code' => ['requestcode', 'request code', 'no permintaan'],
    'kode_dosen' => ['kode dosen', 'kode_dosen', 'lecturer code'],
    // ... add more translations
],
```

## 🧪 Testing Scenarios

### Unit Tests Provided

```bash
php artisan test tests/Feature/ColumnMappingTest.php --filter fm_mapping_config
php artisan test tests/Feature/ColumnMappingTest.php --filter suggest_mapping_exact_match
php artisan test tests/Feature/ColumnMappingTest.php --filter validate_mapping
```

### Manual Testing Checklist

- [ ] **Exact Match**: File with standard column names
  - File: Sample FM dengan headers standard
  - Expected: All columns auto-mapped dengan 100% accuracy

- [ ] **Fuzzy Match**: File dengan headers variant
  - File: FM dengan header "Kode Pengajar" vs "Kode Dosen"
  - Expected: Auto-matched dengan warning/fuzzy icon

- [ ] **Missing Optional**: File tanpa some optional columns
  - File: FM tanpa "First Author"
  - Expected: Mapping OK, warning for optional field missing

- [ ] **Missing Required**: File tanpa required column
  - File: FM tanpa "Submitted"
  - Expected: Validation error, can't confirm

- [ ] **Wrong Sheet**: File dengan berbeda sheet names
  - File: MALANG sheet renamed to "DATA"
  - Expected: System tries multiple sheet names, user can select manual

- [ ] **Multiple Files**: Upload 2-3 files sekaligus
  - Expected: Each file opened modal sequentially
  - Each mapping stored independently

- [ ] **Mobile Responsive**: Test di mobile browser
  - Expected: Modal responsive, selects readable

- [ ] **Error Handling**: Upload corrupted/invalid file
  - Expected: Modal shows error, user can select different file

### Browser DevTools Testing

**Network Tab:**
- POST /preview-columns
  - Request: FormData dengan file, type, csrf-token
  - Response: JSON {success, headers, preview_data, suggested_mapping, config}

- POST /validate-mapping (optional - called on change)
  - Request: JSON {type, mapping}
  - Response: JSON {valid, errors, warnings}

**Console:**
- No errors saat open modal
- No errors saat confirm mapping
- No errors saat submit form

**Storage:**
- confirmedMappings object visible di console
- Persist across modal close/reopen

## 📈 Performance Optimization Tips

### For Large Files

**Problem:** Large Excel files (>5MB) slow to read

**Solutions:**
1. Limit preview to first 1000 rows (already done)
2. Cache file parsing result
3. Use streaming instead of loading entire file

**Implementation:**
```php
// Add caching
$preview = Cache::remember(
    'file_preview_'.md5_file($path),
    60*5,  // 5 minutes
    fn() => $this->service->preview($path)
);
```

### For Many Columns

**Problem:** 50+ columns → slow rendering

**Solutions:**
1. Virtualize table rows (scroll only visible rows)
2. Sticky headers
3. Collapsible sections by required/optional

### For Repeated Mapping

**Problem:** User import 10 files with same structure

**Solutions:**
1. Save mapping template
2. Reuse mapping untuk file berikutnya
3. Remember mapping per file type

## 🚨 Error Handling

### Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "Sheet MALANG atau Detail tidak ditemukan" | File Excel tidak punya expected sheet | Pastikan sheet naming sesuai |
| "Tidak ada baris pada sheet" | Sheet kosong atau hanya header | Pastikan data tersimpan |
| "Kolom X tidak ditemukan" | Required column missing | Ubah mapping atau fix file |
| Modal tidak muncul | Bootstrap JS issue | Check console errors |
| Mapping tidak tersimpan | Form submit sebelum confirm | Ensure confirm button clicked |
| CSRF token mismatch | Session expired | Reload page & retry |

## 📞 Support & Debugging

### Enable Debug Mode

Add to `.env`:
```
APP_DEBUG=true
LOG_CHANNEL=single
```

### View Logs

```bash
tail -f storage/logs/laravel.log

# Filter untuk column mapping
grep -i "column\|mapping" storage/logs/laravel.log
```

### Check Routes

```bash
php artisan route:list --path=backoffice/importrectorate
```

### Inspect Database

```sql
-- Check imports table structure
DESCRIBE publication_imports;
DESCRIBE research_imports;
DESCRIBE student_publication_imports;

-- Check if mapping data saved (if you add mapping storage)
SELECT * FROM publication_imports LIMIT 1\G
```

## 📚 Related Documentation

- [Main Documentation](./COLUMN_MAPPING_PREVIEW.md)
- [Laravel Documentation](https://laravel.com/docs)
- [Bootstrap 5 Modal](https://getbootstrap.com/docs/5.0/components/modal/)
- [Aspera Spreadsheet XLSX](https://github.com/Aspera/php-spreadsheet-reader)

---

**Last Updated:** 21 Oktober 2026
**Version:** 1.0
**Ready for Production:** ✅ Yes (with testing)
