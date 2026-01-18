# Repository Guidelines

## Project Structure & Module Organization
This is a Laravel 12 application with modular packages.
- `app/`: core application code (controllers, models, jobs, etc.).
- `Modules/`: feature modules via `nwidart/laravel-modules` (each module has its own `composer.json` and resources).
- `routes/`: route files (`web.php`, `api.php`, etc.).
- `resources/`: Blade views, frontend assets, and static resources.
- `public/`: web root, compiled assets, entry point.
- `database/`: migrations, factories, seeders.
- `tests/`: PHPUnit tests (`Unit/`, `Feature/`).

## Build, Test, and Development Commands
- `composer install`: install PHP dependencies.
- `npm install`: install frontend tooling (Vite, Tailwind).
- `php artisan serve`: run the local dev server.
- `composer run dev`: run server, queue, logs, and Vite concurrently.
- `npm run dev`: Vite dev server only.
- `npm run build`: build production assets with Vite.
- `php artisan test` or `vendor/bin/phpunit`: run test suites.

## Coding Style & Naming Conventions
- Indentation: 4 spaces (see `.editorconfig`).
- PHP classes use `StudlyCase`, methods/variables use `camelCase`.
- Filenames: class files match class names; Blade views use `snake_case.blade.php`.
- Formatting: use Laravel Pint when available (`vendor/bin/pint`).

## Testing Guidelines
- Framework: PHPUnit (see `phpunit.xml`).
- Test locations: `tests/Unit` and `tests/Feature`.
- Naming: `*Test.php` classes, mirror Laravel conventions.
- Default test DB uses in-memory SQLite; keep tests isolated and deterministic.

## Commit & Pull Request Guidelines
- Commit messages: short, imperative, sentence case (e.g., `Update logo`, `Fix XLSX reader`); avoid prefixes unless needed.
- PRs should include: concise summary, test evidence (command/output), and screenshots for UI changes.
- Link relevant issues or tickets if applicable.

## Configuration & Security Tips
- Copy `.env.example` to `.env` for local setup; never commit secrets.
- Module-level dependencies are merged from `Modules/*/composer.json`; update those when a module requires new packages.
