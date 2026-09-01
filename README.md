# Tabarak Trading V2

Tabarak Trading V2 is a fresh wholesale food catalogue and administration platform. It provides a public product catalogue, protected administration, reusable media handling, and a human-reviewed AI-assisted bulk image import workflow.

This repository is independent from every previous Tabarak Trading codebase. It does not include checkout, payments, customer accounts, live inventory, or deployment configuration. Gemini image analysis is optional and disabled until a server-side API key is configured.

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

Create an empty database named `tabarak_trading_catalogue_v2`, then update the `DB_*` values in `.env` if your local database credentials differ from the defaults.

```sql
CREATE DATABASE tabarak_trading_catalogue_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
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

To use another port, for example when port 8000 is occupied:

```bash
php artisan serve --host=127.0.0.1 --port=8001
```

Then open `http://127.0.0.1:8001` or `http://127.0.0.1:8001/admin/login`.

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
- `app/Services/Imports` defines `ProductImageAnalyzerInterface`, the manual placeholder, the default Gemini implementation, and an optional OpenAI implementation.
- `app/Jobs/AnalyzeImportItem.php` analyzes each image independently without blocking the upload request.

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

## AI-assisted bulk image imports

Open **Admin → Bulk Import**, choose any practical number of images in one selection, and submit the batch. The browser uploads large selections in configurable groups instead of placing every file in one HTTP request. Each validated image becomes a reusable media record and import item, and an independent `AnalyzeImportItem` queue job starts immediately. The review page polls for results and reveals names and package details as jobs finish.

Each selected image may be JPG, PNG, WebP, or SVG and is limited to 8 MB by default. SVG files are sanitized and rasterized to PNG in the administrator's browser before upload so executable SVG markup is never stored or sent to the analyzer. Change `IMPORT_UPLOAD_CHUNK_SIZE` or `IMPORT_IMAGE_MAX_SIZE_KB` when the server environment needs different limits.

Automatic analysis needs a Gemini API key. Create a free-tier key in [Google AI Studio](https://aistudio.google.com/apikey) and place it only in the local `.env` file:

```dotenv
PRODUCT_IMAGE_ANALYZER=gemini
GEMINI_API_KEY=your_server_side_api_key
GEMINI_VISION_MODEL=gemini-3.5-flash-lite
```

Then reload configuration and restart long-running queue workers:

```bash
php artisan optimize:clear
php artisan queue:restart
php artisan queue:work --tries=3 --timeout=120
```

Never commit `.env` or expose the key to Vue/browser code. The provider returns product name, brand, category, weight, label text, SKU/barcode, flavor, packaging, pack quantity, description, confidence, and warnings. Results are drafts only and are never published automatically. The approval and transactional product-creation step remains the next workflow phase.

If a batch was processed while the placeholder analyzer was active, open its review page and click **Run Gemini analysis**. Eligible manual-review items are reset and queued without uploading the images again.

## Security notes

- Public registration and customer authentication routes are intentionally absent.
- All admin routes require both authentication and the admin middleware.
- Admin mutations are guarded by Form Request authorization and policies.
- Uploads validate MIME/type and size; original filenames are retained only as metadata.
- Eloquent/query builder provides parameterized database access.
- Production environments should use `APP_DEBUG=false`, unique database credentials, HTTPS, a non-default admin password, and a supervised queue worker.
