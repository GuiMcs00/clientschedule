# ClientSchedule — Scheduling API for Psychologist Practice

A real-world **scheduling + client management** API for a psychologist practice (patients, one-off and recurring sessions), with planned features such as clinical report attachments and email notifications.
Built with Laravel 12 + PostgreSQL and modern PHP (strong typing, attributes) to deliver a maintainable, production-ready foundation.

**Status:** Customer CRUD ✅ | Appointments & Recurrence ✅ (POC) | UI planned 🎨

---

## Problem → Solution

### The problem

Psychologists often manage patients and weekly sessions using spreadsheets/WhatsApp, which makes it hard to:

* visualize the calendar reliably,
* prevent double bookings,
* reactivate returning patients without losing history.

### The solution

**ClientSchedule** provides a clean REST API to:

* register patients,
* create one-off appointments,
* define weekly recurrence rules (series + weekday slots),
* keep data consistent using validation and database constraints.

---

## Highlights

* **Customer management** (CRUD)
* **Soft delete + restore** (reactivate returning patients)
* **Appointments** (one-off)
* **Recurring series (weekly)** with weekday/time slots
* **Versioned API** (`/api/v1`)
* **Validation via FormRequest**
* **Consistent output via API Resources**
* **PostgreSQL-ready design** (built for real constraints)

---

## Tech Stack

* PHP 8.2+
* Laravel 12
* Eloquent ORM
* PostgreSQL
* (Optional) Node/Vite for future UI

---

## API Overview

Base URL: `http://127.0.0.1:8000/api/v1`

### Customers

* `GET /customers`
* `POST /customers`
* `GET /customers/{id}`
* `PATCH /customers/{id}`
* `DELETE /customers/{id}` (soft delete)
* `POST /customers/{id}/restore` (reactivate)
* `DELETE /customers/{id}/force` (hard delete)

**Payload example**

```json
{
  "name": "Ana Souza",
  "email": "ana@email.com",
  "phone": "+55 61 99999-9999"
}
```

### Appointments (scoped by customer)

* `GET /customers/{customer}/appointments?from=...&to=...` (calendar range)
* `POST /customers/{customer}/appointments` (one-off)
* `GET /customers/{customer}/appointments/{appointment}`
* `PATCH /customers/{customer}/appointments/{appointment}`
* `DELETE /customers/{customer}/appointments/{appointment}`

**Create appointment**

```json
{
  "title": "Session",
  "notes": "Optional notes",
  "starts_at": "2026-02-04T10:00:00-03:00",
  "ends_at": "2026-02-04T11:00:00-03:00",
  "series_id": null
}
```

### Recurrence (Appointment Series)

* `GET /customers/{customer}/series`
* `POST /customers/{customer}/series`
* `GET /customers/{customer}/series/{series}`
* `PATCH /customers/{customer}/series/{series}`
* `DELETE /customers/{customer}/series/{series}` (**deactivates**: `is_active=false`)

**Create weekly series**

```json
{
  "title": "Weekly Session",
  "timezone": "America/Sao_Paulo",
  "starts_on": "2026-02-01",
  "ends_on": null,
  "is_active": true,
  "weekdays": [
    { "weekday": 3, "start_time": "15:00", "end_time": "16:00" }
  ]
}
```

> Recurrence is generated for a limited horizon (e.g., 12 weeks).
> A future improvement is a scheduled “refill” job to extend occurrences automatically.

---

## Key Technical Decisions

* **Soft delete as default**: customers aren’t physically removed; they can be restored.
* **Reactivation workflow**: creating a customer with an email that exists in a soft-deleted record can restore/update it (implemented in customer logic).
* **Request validation**: FormRequests define the payload contract clearly and return 422 automatically.
* **Stable response format**: API Resources control output shape and allow safe evolution.
* **Recurrence model**:

  * `appointment_series` stores series metadata (title/notes/timezone/date range)
  * `appointment_series_weekdays` stores weekly slots
  * `appointments` stores actual occurrences (one-off or generated), enabling fast calendar queries.

---

## Roadmap (next PRs)

* [ ] Rolling window generation (“refill” occurrences for active series)
* [ ] Calendar endpoints optimization (`from/to` exclusive range, lightweight mode)
* [ ] Swagger/OpenAPI improvements (separate schema files, less controller noise)
* [ ] Frontend (calendar view + day details)
* [ ] Authentication & Policies (multi-therapist scenario)

---

## Requirements

PHP 8.2+ with extensions:

* `xml`, `dom` (often `php-xml`)
* `pdo_pgsql`, `pgsql` (often `php-pgsql`)

Also:

* Composer
* PostgreSQL 14+ (or compatible)
* (Optional) Node 18+ / npm

---

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### PostgreSQL

```bash
sudo -u postgres psql
```

```sql
CREATE DATABASE clientschedule;
CREATE USER clientschedule_user WITH PASSWORD 'password';
GRANT ALL PRIVILEGES ON DATABASE clientschedule TO clientschedule_user;
ALTER DATABASE clientschedule OWNER TO clientschedule_user;
\q
```

Update `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=clientschedule
DB_USERNAME=clientschedule_user
DB_PASSWORD=password

SESSION_DRIVER=file
```

Run migrations:

```bash
php artisan migrate
```

---

## Run

```bash
php artisan serve
```

Open: `http://127.0.0.1:8000`

---

## Useful Commands

```bash
php artisan route:list
php artisan optimize:clear
php artisan migrate:fresh
```

---

## Why this project

This repository is intentionally scoped as a **POC** to demonstrate:

* Laravel fundamentals (routing/controllers/resources/requests)
* PostgreSQL-backed scheduling constraints
* clear code organization and tradeoffs for production readiness

---

### Pequenos ajustes que eu recomendo antes de enviar pro recrutador

1. Trocar “Mini scheduling + client management…” do topo por essa seção “Problem → Solution” (já fiz acima).
2. Manter “Architecture and Project Organization” fora do README (ou reduzir) — isso geralmente vira ruído; o recrutador quer **o que faz e como roda**.
3. Se você tiver Swagger pronto, adicione uma linha curta:

   * “OpenAPI available at …” (mas só se estiver estável).

Se quiser, eu também monto um **email curto e forte** pro recrutador, mencionando o repositório como POC e destacando 3 bullets técnicos (sem textão).
