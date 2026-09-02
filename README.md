# Tabarak Trading V2

Tabarak Trading V2 is a fresh wholesale food catalogue and administration platform. It provides a public product catalogue, protected administration, reusable media handling, and a human-reviewed AI-assisted bulk image import workflow.

This repository is independent from every previous Tabarak Trading codebase. It includes a USD cart and invoice workflow, transactional stock reservation, deletion audit notices, and an AI-to-OCR-to-manual bulk import workflow. It does not process online payments or provide customer accounts.

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
- Optional: Tesseract OCR installed on the server for free local OCR fallback

Install Tesseract with `sudo apt-get install tesseract-ocr` on Ubuntu/Debian. On Windows, install a Tesseract OCR distribution and set `OCR_TESSERACT_BINARY` to its full executable path when it is outside the system PATH.

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

For production deployment, also migrate the database, cache configuration, and keep a queue worker running:

```bash
php artisan migrate --force
php artisan optimize
php artisan queue:work --tries=3 --timeout=120
```

On shared hosting without a persistent worker, schedule `php artisan queue:work --stop-when-empty --tries=3 --timeout=120` every minute. Without a queue worker, uploaded images remain queued and neither AI nor OCR analysis will start.

Check the configured fallback chain without spending an AI request:

```bash
php artisan imports:diagnose
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
- `categories`, `brands`, `products`, and `product_variants`, including optional tracked stock
- `media` and `product_images`
- `import_batches` and `import_items`
- `orders`, `order_items`, and permanent `order_deletion_notices`
- `settings`
- Laravel `cache`, `jobs`, `job_batches`, and `failed_jobs` tables

Media records are reusable across products, category imagery, brand logos, and import drafts. Stored files use generated UUID filenames and are served through an ID-bound public media route, so local development does not depend on symlink support. SHA-256 checksums support exact-file duplicate detection. Deleting products does not delete reusable media.

## AI-assisted bulk image imports

Open **Admin → Bulk Import**, choose any practical number of images in one selection, and submit the batch. The browser uploads large selections in configurable groups instead of placing every file in one HTTP request. Each validated image becomes a reusable media record and import item, and an independent `AnalyzeImportItem` queue job starts immediately. The review page polls for results and reveals names and package details as jobs finish.

Each selected image may be JPG, PNG, WebP, or SVG and is limited to 8 MB by default. SVG files are sanitized and rasterized to PNG in the administrator's browser before upload so executable SVG markup is never stored or sent to the analyzer. Change `IMPORT_UPLOAD_CHUNK_SIZE` or `IMPORT_IMAGE_MAX_SIZE_KB` when the server environment needs different limits.

Automatic analysis can use a Gemini API key as its primary detector. Create a free-tier key in [Google AI Studio](https://aistudio.google.com/apikey) and place it only in the server `.env` file:

```dotenv
PRODUCT_IMAGE_ANALYZER=gemini
GEMINI_API_KEY=your_server_side_api_key
GEMINI_VISION_MODEL=gemini-3.5-flash-lite
OCR_ENABLED=true
OCR_TESSERACT_BINARY=tesseract
OCR_LANGUAGE=eng
```

Then reload configuration and restart long-running queue workers:

```bash
php artisan optimize:clear
php artisan queue:restart
php artisan queue:work --tries=3 --timeout=120
```

Never commit `.env` or expose the key to Vue/browser code. The fallback sequence is primary AI, local Tesseract OCR, then manual admin review. Provider errors do not delete or reject the uploaded image. Results are drafts only and are never published automatically.

If a batch was processed before analysis was configured, open its review page and click **Run analysis again**. Eligible manual-review items are reset and queued without uploading the images again.

## Security notes

- Public registration and customer authentication routes are intentionally absent.
- All admin routes require both authentication and the admin middleware.
- Admin mutations are guarded by Form Request authorization and policies.
- Uploads validate MIME/type and size; original filenames are retained only as metadata.
- Eloquent/query builder provides parameterized database access.
- Production environments should use `APP_DEBUG=false`, unique database credentials, HTTPS, a non-default admin password, and a supervised queue worker.
