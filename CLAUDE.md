# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

RCDC Malang: a Laravel 12 (PHP 8.2+) app for a university research & community development center. It has public KPI dashboards and a backoffice where staff upload Excel workbooks (publications, research grants, lecturers, community development). UI text, validation messages, docs and commit messages are in Indonesian. `AGENTS.md` has the general repo conventions. The README is the stock Laravel one.

## Commands

```bash
composer run dev                 # serve + queue:listen + pail + vite, all at once
vendor/bin/pint                  # format PHP (vendor/bin/pint --dirty for changed files only)

# PHP tests. Laragon's PHP CLI ships pdo_sqlite but doesn't enable it. Without -d, DB tests markTestSkipped.
php -d extension=pdo_sqlite vendor/bin/phpunit
php -d extension=pdo_sqlite vendor/bin/phpunit --filter ImportRectorateDeletionTest
php -d extension=pdo_sqlite vendor/bin/phpunit tests/Feature/ColumnMappingTest.php

# JS tests (node:test, no npm deps)
node --test tests/js/
node --test tests/js/rectorate-mapping.test.mjs
```

Use `vendor/bin/phpunit` instead of `php artisan test`, because `artisan test` doesn't pass the `-d extension` flag to the child process. After you change `.env` import flags, run `php artisan config:clear`.

## Architecture

### Two separate front ends
1. **Public pages.** These are plain controllers in `app/Http/Controllers` that call one service and return one Blade view: `/` landing, `/kpi-publikasi`, `/dashboard-comdev`, `/dashboard-lrc`, `/dashboard-cbdc`, `/research-gallery`. The header (`landing/components/header.blade.php`) groups the dashboards under one Dashboard dropdown; its toggle lives in the theme's `public/landing/assets/js/main.js`. Their CSS/JS is hand-written in `public/css/*.css` and `public/js/*.js`. You edit those files directly, with no build step. Views cache-bust them with `?v={{ filemtime(...) }}`. Vite/Tailwind is set up but only used by `welcome.blade.php`.
2. **Backoffice SPA** at `/dashboard`. `DashboardController` renders the `dashboard.index` shell (Metronic theme, jQuery, DataTables, all prebuilt under `public/assets/backoffice`). It builds the sidebar HTML from `sys_menu` / `sys_role_menu` / `sys_role`. Page loading works like this:
   - The Navigo router in `resources/views/dashboard/javascript.blade.php` maps `/dashboard/:page` to a POST to `/loadpage` with `destination`.
   - `/loadpage` redirects to `backoffice/{destination}`, which is a module's `index()`.
   - The module returns `loadPage('alias::index')`, a global helper that returns JSON `{page: base64(html)}`. The shell decodes it into `#kt_post`.
   - So a menu's `menu_kode` must equal the module's route prefix, and module views are HTML fragments, not full pages.
   - Auth is mostly stubbed. The role is hardcoded (`OWN001` when logged in, `DSN001` when not; `Auth::user()->role_id` is commented out). `/change_perms` logs in user #1.

### Modules (`Modules/*`, nwidart/laravel-modules)
- `modules_statuses.json` enables or disables each module.
- Each module registers `routes/web.php` under `Route::prefix('backoffice/<alias>')` with the `web` middleware. Views are namespaced `<alias>::`.
- Page JS is an inline `<script>` in `resources/views/*javascript.blade.php` partials and uses `{{ route() }}` / `{{ csrf_token() }}`. The `tests/js/*.test.mjs` files read those Blade files, strip the Blade tags, and run the script in `node:vm` with a small fake DOM. If you change a module's JS or markup IDs, run the JS tests.
- CSRF is disabled for `/dashboard*`, `/backoffice/*` and `/loadpage` (`bootstrap/app.php`). One exception: ImportRectorate deletion uses its own `VerifyImportDeletionCsrfToken` middleware.
- Module controllers stay thin. Domain logic lives in `app/Services/*` (Publications, Research, Lecturers, CommunityDevelopment, Reports, ColumnMapping, Imports). The KPI-calculation modules lean on the global functions in `app/Helpers/Super.php`, which is autoloaded via composer `files`. Examples: `getScoreNew`, `TP12Func`, `AA2Func`, `L2Func`, and `select_table` (DataTables).

### Workbook import pipeline
This is the same across ImportRectorate (FM/MHS publications, hibah), Dosen (Data Center, education status) and ImportPKM (comdev ratio/involvement, UMKM partners):
1. **Preview endpoint.** It reads the `.xlsx` (with `aspera/xlsx-reader`; `.xlsb` goes through the custom `XlsbReader`). It returns headers, sample rows and a `suggested_mapping` from `ColumnMapperService`. That service holds the per-type `required` / `optional` / `aliases` column definitions.
   The response also lists every sheet. An optional `sheet` (PKM Rasio/Keterlibatan: a `sheets` JSON per role) previews another sheet, and the upload sends the confirmed sheet back. Without one, each importer falls back to its default sheet name (Malang/Raw/Detail/…). ImportRectorate FM/MHS also read supporting sheets (KPI, Realization Template, Scoring PI, LIST, TITLE; defined in `WorkbookSheetRoles`): the preview returns them as `extra_roles`, and the upload sends `extra_sheets[<type>]` with each one's sheet (or null) and column mapping. Without `extra_sheets` they are still found by name.
2. **The user confirms the mapping.** Column indexes start at 0 and header rows start at 1. One source column may feed several target fields.
3. **Upload.** It runs the importer (e.g. `PublicationImporter`, `RectorateResearchImporter`, `PkmWorkbook`) inside a DB transaction and replaces only that source and period. Each run is recorded in a `*_imports` history table, and the response includes a re-rendered `history_html`.
4. **`ImportPolicy` + `config/imports.php`.** These read the `IMPORT_*_FORCE` env flags. With force off, any bad row throws a `ValidationException` and existing data is kept. With force on, bad rows become warnings or skipped rows. `phpunit.xml` pins every flag to `false`. Full rules are in `docs/imports.md`.

### Database
- The core legacy tables are **not** created by migrations. They come from the `rcdc.sql` dump: `database_dosen_new`, `rectorate_dosen`, `rectorate_mahasiswa`, `pkm_dosen`, `ce_pkm_dosen`, `identitas_dosen`, `sys_menu`, `sys_role`, `sys_role_menu`, and others.
- `database/migrations` only adds newer tables and columns on top: import tracking, KPI entries, lecturer snapshots, comdev, partners. Local dev runs on MySQL (Laragon).
- Because of this, Feature tests don't use `RefreshDatabase`. Their `setUp()` does the following, and new tests should follow the same pattern:
  - creates the legacy tables they need with `Schema::create`
  - runs specific migrations with `(require database_path('migrations/<file>.php'))->up()`
  - builds `.xlsx` fixtures in memory with `tests/Support/CreatesWorkbook`

### Other notes
- `routes/api_remodeled_dashboard.php` is **not registered** in `bootstrap/app.php`, so its routes are inactive.
- `docs/*.md` holds the Indonesian feature specs: imports, publication KPI, rectorate research, monthly uploads/reports, lecturer visibility. Read the matching one before changing a feature.
- The root-level `*.md` notes (START_HERE, IMPLEMENTATION_*, FIX_*) cover the column-mapping preview work. The root `*.html` files are standalone dashboard prototypes, not served by the app.
- In practice, commits follow `type(scope): Deskripsi` in Indonesian, e.g. `feat(comdev): Penambahan dashboard comdev`.
