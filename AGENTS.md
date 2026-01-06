# Repository Guidelines

## Project Structure & Module Organization
- Laravel 12 app using nwidart modules; domain logic lives in `Modules/*` with each module typically holding its own routes, views, and config. Shared services reside in `app/` (helpers in `app/Helpers/Super.php`).
- HTTP entrypoints: `routes/web.php` for core web routes, plus module routes (e.g., `Modules/<Name>/Routes/web.php`).
- UI assets and Blade templates are in `resources/` (JS/CSS via Vite + Tailwind 4, views in `resources/views` and module view folders). Public build output is served from `public/`.
- Database layer sits in `database/migrations` with seeds in `database/seeders` and factories under `database/factories`. Tests live in `tests/Feature` and `tests/Unit`.

## Build, Test, and Development Commands
- `composer install` and `npm install` to restore PHP and frontend dependencies.
- `php artisan migrate --graceful` to apply schema updates using your `.env` connection.
- `composer run dev` to start the PHP server, queue listener, log viewer (pail), and Vite in parallel for local development.
- `php artisan serve` (or your web server of choice) to serve the app; `npm run dev` for Vite-only asset watching; `npm run build` for production assets.

## Coding Style & Naming Conventions
- Honor `.editorconfig`: LF line endings, UTF-8, 4-space indentation (2 for YAML), final newline, and no trailing whitespace.
- PHP follows PSR-4 autoloading: namespaces `App\\` and `Modules\\`. Classes use StudlyCase, methods/properties camelCase, and config keys snake_case.
- Prefer Laravel facilities (Eloquent, facades, request validation) and keep module boundaries clear—avoid cross-module coupling without contracts.
- Run `./vendor/bin/pint` before pushing to enforce formatting.

## Testing Guidelines
- Use PHPUnit via `php artisan test` (recommended) or `./vendor/bin/phpunit`.
- Place request/route tests in `tests/Feature` and logic-heavy units in `tests/Unit`; name files `*Test.php`.
- Keep fixtures deterministic; seed with factories where possible and reset the DB using `RefreshDatabase` or database transactions.

## Commit & Pull Request Guidelines
- Recent history uses short, action-oriented messages (e.g., "Update main old", "Bypass CSRF"); keep each commit focused on one change set.
- PRs should describe scope, linked issues/task IDs, setup or migration notes, and screenshots for UI-impactful changes.
- Call out any queue/config additions, new env vars, and how to roll back if needed.

## Security & Configuration Tips
- Copy `.env.example` to `.env`, set `APP_KEY`, database credentials, and queue/redis settings before running commands.
- Avoid committing secrets or generated artifacts; use `storage/` for temporary files and ensure public uploads are validated and stored via Laravel's filesystem.
