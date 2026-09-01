# Tabarak Trading V2

Tabarak Trading V2 is a fresh wholesale food catalogue and administration platform. It provides a public product catalogue, protected administration, reusable media handling, and the database/job foundation for a future human-reviewed AI image import workflow.

This repository is independent from every previous Tabarak Trading codebase. It does not include checkout, payments, customer accounts, live inventory, deployment configuration, or an external AI provider.

## Stack

- PHP 8.2+ and Laravel 12
- MySQL or MariaDB
- Vue 3 Composition API with TypeScript
- Inertia.js 2, Vite, and Tailwind CSS
- Laravel database queues for import jobs

## Requirements

- PHP 8.2 or newer with PDO MySQL, fileinfo, OpenSSL, and GD
- Composer 2
- Node.js 20+ and npm
- MySQL 8+ or MariaDB 10.4+

## Installation

```bash
composer install
copy .env.example .env
php artisan key:generate
npm install
```

Create an empty database named `tabarak_trading_v2`, then update the `DB_*` values in `.env` if your local database credentials differ from the defaults.

```sql
CREATE DATABASE tabarak_trading_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Create the schema and development catalogue:

```bash
php artisan migrate:fresh --seed
```

The development admin account created by the seeder is:

- Email: `admin@tabaraktrading.co`
- Password: `password`

Change this password before any environment is exposed to other users.

## Development

Run the Laravel server, Vite, and queue worker in separate terminals:

```bash
php artisan serve
npm run dev
php artisan queue:work
```

Open `http://localhost:8000` for the storefront and `http://localhost:8000/admin/login` for administration.

## Production build

```bash
npm run build
```

## Tests and formatting

The automated suite uses an in-memory SQLite database. It never resets the configured local MySQL database.

```bash
php artisan test
vendor/bin/pint --test
```

## Architecture

Backend responsibilities are split into small, focused layers:

- `app/Actions` contains transactional catalogue, media, and import operations.
- `app/DTOs` defines reliable action and analyzer inputs/results.
- `app/Enums` owns product and import state values.
- `app/Http/Controllers/Storefront` returns public catalogue pages.
- `app/Http/Controllers/Admin` contains protected resource controllers.
- `app/Http/Requests` owns server-side authorization and validation rules.
- `app/Models` defines normalized relationships and explicit mass-assignment fields.
- `app/Policies` denies mutations unless the authenticated user is an administrator.
- `app/Services/Products` owns reusable query, presentation, and slug behavior.
- `app/Services/Imports` defines `ProductImageAnalyzerInterface` and the provider-free placeholder implementation.
- `app/Jobs/AnalyzeImportItem.php` is the queue seam for a future external analyzer.

Frontend responsibilities are similarly separated:

- `resources/js/Layouts` contains storefront, admin, and admin-auth shells.
- `resources/js/Pages/Storefront` contains route-level catalogue screens.
- `resources/js/Pages/Admin` contains route-level administration screens.
- `resources/js/Components/Storefront`, `Admin`, and `Shared` contain reusable UI pieces.
- `resources/js/Composables` contains page-independent interaction state.
- `resources/js/types` and `resources/js/Utils` contain TypeScript contracts and formatting helpers.

Routes are split between `routes/storefront.php` and `routes/admin.php`, with `routes/web.php` acting only as the entry point.

## Database

The application tables are:

- `users`, `password_reset_tokens`, and `sessions`
- `categories`, `brands`, `products`, and `product_variants`
- `media` and `product_images`
- `import_batches` and `import_items`
- `settings`
- Laravel `cache`, `jobs`, `job_batches`, and `failed_jobs` tables

Media records are reusable across products, category imagery, brand logos, and import drafts. Stored files use generated UUID filenames and are served through an ID-bound public media route, so local development does not depend on symlink support. SHA-256 checksums support exact-file duplicate detection. Deleting products does not delete reusable media.

## Import workflow foundation

An administrator can create a batch and upload multiple validated images. Each image becomes a media record and related import item, then an `AnalyzeImportItem` job is dispatched. The current placeholder analyzer returns no product guesses and adds a manual-review warning.

A future provider must implement `App\Services\Imports\ProductImageAnalyzerInterface`.

The approval and product-creation workflow should be implemented as a later phase. External analysis must continue producing drafts only; it must never publish products automatically.

## Security notes

- Public registration and customer authentication routes are intentionally absent.
- All admin routes require both authentication and the admin middleware.
- Admin mutations are guarded by Form Request authorization and policies.
- Uploads validate MIME/type and size; original filenames are retained only as metadata.
- Eloquent/query builder provides parameterized database access.
- Production environments should use `APP_DEBUG=false`, unique database credentials, HTTPS, a non-default admin password, and a supervised queue worker.
