# ClientSchedule (Customer Management)

Mini customer management and scheduling system, developed as an exercise for a **PHP/Laravel** position, focusing on **Laravel + Eloquent + PostgreSQL** and modern PHP features.

## Stack

- PHP 8.2+
- Laravel 12
- Eloquent ORM
- PostgreSQL
- (Optional) Node/Vite for frontend assets

## Features

- **Customers** CRUD
- **Soft Delete** (logical deletion) and **Restore** (reactivation)
- **Force Delete** (permanent deletion)
- Versioned API (`/api/v1`)
- Validation via **FormRequest**
- Standardized responses via **API Resources**

---

## Architecture and Project Organization

This project follows Laravel conventions to maintain predictability and facilitate maintenance.

### Main Folders

- `routes/`
    - `api.php` — API routes (stateless)
    - `web.php` — web routes (stateful: sessions/cookies/CSRF)
    - `console.php` — Artisan commands (CLI entrypoints)

- `app/Http/Controllers/`
    - Application controllers (HTTP entry layer)

- `app/Http/Requests/`
    - FormRequests for **input validation and authorization** (`validated()`)

- `app/Http/Resources/`
    - Resources for **JSON output standardization** (API shape)

- `app/Models/`
    - Eloquent models (entity mapping and simple rules/relationships)

- `database/migrations/`
    - Schema versioning (Postgres)

### Flow (API)

HTTP Request → `routes/api.php` → Controller → FormRequest (`validated()`) → Model/Eloquent → Resource → JSON Response

### Where is the "Customer CRUD"

- Model: `app/Models/Customer.php`
- Controller: `app/Http/Controllers/Api/CustomerController.php`
- Requests:
    - `app/Http/Requests/StoreCustomerRequest.php`
    - `app/Http/Requests/UpdateCustomerRequest.php`

- Resource:
    - `app/Http/Resources/CustomerResource.php`

- Routes:
    - `routes/api.php`

---

## Requirements

- PHP 8.2+ with extensions:
    - `xml`, `dom` (usually via `php-xml`)
    - `pdo_pgsql`, `pgsql` (usually via `php-pgsql`)

- Composer
- PostgreSQL 14+ (or compatible)
- (Optional) Node 18+ and npm for Vite

## Installation

Clone the repository and install dependencies:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## Database Setup (PostgreSQL)

Create the database and user (example):

```bash
sudo -u postgres psql
```

Inside `psql`:

```sql
CREATE DATABASE clientschedule;
CREATE USER user WITH PASSWORD 'password';
GRANT ALL PRIVILEGES ON DATABASE clientschedule TO user;
ALTER DATABASE clientschedule OWNER TO user;
\q
```

Edit the `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=clientschedule
DB_USERNAME=user
DB_PASSWORD=password

SESSION_DRIVER=file
```

> Note: `SESSION_DRIVER=file` avoids dependency on the `sessions` table during initial setup.

Run migrations:

```bash
php artisan migrate
```

---

## Run the Project

Local server:

```bash
php artisan serve
```

The application will be available at `http://127.0.0.1:8000`.

### (Optional) Frontend with Vite

If using Vite:

```bash
npm install
npm run dev
```

---

## API

Base URL:

- `http://127.0.0.1:8000/api/v1`

### Customers

- `GET /customers` — list customers (only active; soft deleted don't appear)
- `POST /customers` — create customer
    - If a soft deleted customer with the same email exists, the system **reactivates and updates** (automatic reactivation)

- `GET /customers/{id}` — detail
- `PATCH /customers/{id}` — update
- `DELETE /customers/{id}` — soft delete
- `POST /customers/{id}/restore` — restore (reactivate)
- `DELETE /customers/{id}/force` — permanently delete

#### Payload example (POST /customers)

```json
{
    "name": "Ana Souza",
    "email": "ana@email.com",
    "phone": "+55 61 99999-9999"
}
```

---

## Rules and Technical Decisions

- **Soft Delete**: Customers are not physically removed by default; the `deleted_at` field is filled.
- **Unique email among active**: validation prevents email duplication in active customers.
- **Reactivation**: when creating with the email of a soft deleted customer, the record is restored and updated.
- **Validation**: FormRequests ensure consistent payload without manual conditionals in the controller.
- **Controlled output**: Resources explicitly define the response shape.

---

## Useful Commands

List routes:

```bash
php artisan route:list
```

Clear caches:

```bash
php artisan optimize:clear
```

Recreate database from scratch (deletes everything):

```bash
php artisan migrate:fresh
```
