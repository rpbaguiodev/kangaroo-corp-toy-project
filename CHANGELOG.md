# Changelog

All notable changes to this project will be documented in this file.

---

## [Unreleased] — branch: `feature/search_improvement`
_Date: 2026-02-25_

> ⚠️ **IMPORTANT:** Run the following commands before seeding or using the application.
>
> **1. Run migrations:**
> ```bash
> ./vendor/bin/sail artisan migrate
> ```
>
> **2. Seed the database:**
> ```bash
> ./vendor/bin/sail artisan db:seed --class=CustomerSeeder
> ```
>
> **3. Benchmark search performance (before and after migration):**
> ```bash
> ./vendor/bin/sail artisan benchmark:customer-search
> ```

---

### Fixed

- **Customer delete not working** (`app/Http/Controllers/CustomerController.php`)
  - `destroy()` was missing `$customer->delete()`, so records were never removed from the database despite showing a success message.

- **Feature tests returning 419 (CSRF mismatch)** (`tests/Feature/Customer/CustomerControllerTest.php`)
  - POST/PUT/DELETE test requests were blocked by CSRF verification.
  - Fixed by disabling `ValidateCsrfToken` middleware in the test `setUp()`.

---

### Added

- **Customer Seeder** (`database/seeders/CustomerSeeder.php`)
  - Imports `mock-db-fake-data.sql` (~1.1 million records) directly into MySQL via shell command, bypassing PHP memory limits for large file handling.
  - Truncates the `customers` table before importing to avoid duplicate key conflicts.
  - `mock-db-fake-data.sql` and `mock-db-fake-data.zip` added to `.gitignore`.
  - Run with: `./vendor/bin/sail artisan db:seed --class=CustomerSeeder`

- **FULLTEXT Index Migration** (`database/migrations/2026_02_25_070437_add_fulltext_index_to_customers_table.php`)
  - Adds a MySQL FULLTEXT index on `name`, `email`, `phone`, and `company` columns.
  - Eliminates full table scans caused by leading-wildcard `LIKE "%term%"` queries.

- **Benchmark Artisan Command** (`app/Console/Commands/BenchmarkCustomerSearch.php`)
  - `./vendor/bin/sail artisan benchmark:customer-search` compares `LIKE` vs `FULLTEXT` query performance.
  - Reports average, min, and max execution time in milliseconds over N iterations.
  - Supports `--term` and `--iterations` options.

- **Customer Service** (`app/Services/CustomerService.php`)
  - Extracted search and caching logic from the controller.
  - `search()` — runs FULLTEXT query with 5-minute cache per search term + page.
  - `flushCache()` — clears all cached results; called on every write operation.

- **Actions** (`app/Actions/Customer/`)
  - `CreateCustomerAction` — creates a customer record and flushes the cache.
  - `UpdateCustomerAction` — updates a customer record and flushes the cache.
  - `DeleteCustomerAction` — deletes a customer record and flushes the cache.

- **Form Requests** (`app/Http/Requests/`)
  - `StoreCustomerRequest` — validation rules for creating a customer.
  - `UpdateCustomerRequest` — validation rules for updating a customer.

- **Tests**
  - `tests/Unit/Services/CustomerServiceTest.php` — covers search pagination, caching, and cache flushing.
  - `tests/Unit/Actions/Customer/CreateCustomerActionTest.php` — verifies record creation and cache flush.
  - `tests/Unit/Actions/Customer/UpdateCustomerActionTest.php` — verifies record update and cache flush.
  - `tests/Unit/Actions/Customer/DeleteCustomerActionTest.php` — verifies record deletion and cache flush.
  - `tests/Feature/Customer/CustomerControllerTest.php` — full HTTP tests for all CRUD endpoints and validation.

---

### Improved

- **Controller refactored** (`app/Http/Controllers/CustomerController.php`)
  - Moved all business logic out of the controller into the Service and Action classes.
  - Validation delegated to `StoreCustomerRequest` and `UpdateCustomerRequest`.
  - Controller now only handles HTTP input and responses.

- **Search replaced with FULLTEXT** (`app/Services/CustomerService.php`)
  - Replaced `orWhere('column', 'like', "%term%")` on 4 columns with a single `whereFullText()` call backed by the new index.

- **Search result caching** (`app/Services/CustomerService.php`)
  - Search results cached for 5 minutes per unique term + page combination.
  - Cache invalidated automatically on any create, update, or delete.

- **Frontend search debounce** (`resources/views/customers/index.blade.php`)
  - Search form auto-submits 500ms after the user stops typing, reducing unnecessary requests.
  - Visual feedback (input dims) while the debounce timer is active.
  - Enter key submits immediately; Search button behaviour unchanged.

- **Layout scripts stack** (`resources/views/layouts/app.blade.php`)
  - Added `@stack('scripts')` before `</body>` to support page-specific JavaScript via `@push('scripts')`.
