# Petrol Pump Management System

API-first backend for a single petrol pump. Built for mobile-first admin use (phone), with token authentication and shift-based meter readings. Hardware is not integrated yet.

## System overview

The station has **3 pump units**. Each unit has **2 sides (A/B)**. Each side has **one nozzle** that dispenses petrol or diesel.

Sales are **not entered per vehicle**. An attendant:

1. Starts a shift and records an **opening meter reading** for every nozzle
2. Optionally records **credit sales**, **product sales**, **tanker deliveries**, and **expenses** during the shift
3. Ends the shift with a **closing meter reading** for every nozzle

The system then derives fuel sales:

```
liters_sold = closing_reading - opening_reading
rate        = active fuel_rates row at shift start
total       = liters_sold × rate
```

Tank stock is maintained as:

```
stock = opening_stock + purchases − sales − wastage ± adjustments
```

Closed shift records cannot be edited. Deletes are soft deletes only. Every create/update/delete is written to `audit_logs`.

### Domain decisions

| Topic | Rule |
| --- | --- |
| Open shifts | One station-wide open shift at a time |
| Opening continuity | Next opening reading must match the nozzle's previous closing reading |
| Fuel rate | Exactly one active window per fuel type; ranges cannot overlap |
| Credit sales | Ledger allocations against an open shift; liters cannot exceed meter-derived sales at close |
| Fuel COGS | Weighted average cost from tanker purchases |
| Profit | Fuel profit = sales − weighted COGS; also reported vs raw purchase cost. Product profit = sale − product cost |
| Alerts | Tank `current_stock <= low_level_threshold` |

## Tech stack

- Laravel 13 (API only — no Blade business logic)
- MySQL 8+
- Laravel Sanctum (token auth)
- Services + repositories
- Form Requests + API Resources

## Setup

### Requirements

- PHP 8.3+
- Composer
- MySQL 8+
- PHP extensions: `bcmath`, `pdo_mysql`, `mbstring`, `openssl`

### Install

```bash
git clone <repo-url> petrol-pump-management-system
cd petrol-pump-management-system
composer install
cp .env.example .env
php artisan key:generate
```

### Database

Create an empty schema, then point `.env` at it:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=petrol_pump
DB_USERNAME=root
DB_PASSWORD=
```

```sql
CREATE DATABASE petrol_pump CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php artisan migrate --seed
npm install
npm run dev
php artisan serve
```

**Admin UI:** [http://localhost:8000/admin](http://localhost:8000/admin)  
API base URL: `http://localhost:8000/api/v1`

The Vue admin app is mobile-first and talks only to `/api/v1` with a Sanctum bearer token. `/` redirects to `/admin`. For production assets run `npm run build` instead of `npm run dev`.

### Seeded accounts

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@pump.test` | `password` |
| Attendant | `attendant@pump.test` | `password` |

Seeded station layout:

- Fuel types: petrol, diesel
- Tanks: one per fuel type, with capacity, opening stock, and low-level threshold
- Pumps: Unit 1 (petrol/petrol), Unit 2 (petrol/diesel), Unit 3 (diesel/diesel)
- Sample rates, one credit customer, and one non-fuel product

## Admin UI

Open `http://localhost:8000/admin` and sign in with `admin@pump.test` / `password`.

| Screen | What it calls |
| --- | --- |
| Home | `GET /dashboard` |
| Shift | `POST /shifts/start`, `POST /shifts/{id}/end`, `GET /nozzles` |
| Tanks | `GET /tanks`, `POST /tanks/{id}/transactions` |
| Customers | `GET/POST /customers`, credit sales, payments |
| Products | `POST /products`, stock, sales |
| Expenses | `GET/POST /expenses` |
| Rates | `GET/POST /fuel-rates` |
| Reports | `GET /reports/daily`, `GET /reports/profit` |
| Audit | `GET /audit-logs` |

## API usage

Send `Accept: application/json` and, after login, `Authorization: Bearer {token}`.

Full response examples live in [`docs/sample-api-responses.md`](docs/sample-api-responses.md).

### Auth

```http
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
```

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pump.test","password":"password","device_name":"iphone"}'
```

### Typical shift flow

```http
POST /api/v1/shifts/start
POST /api/v1/meter-readings/opening
POST /api/v1/customers/{id}/credit-sales
POST /api/v1/meter-readings/closing
POST /api/v1/shifts/{id}/end
GET  /api/v1/dashboard
GET  /api/v1/reports/daily
```

`POST /shifts/start` requires opening readings for **all active nozzles**. Closing readings can be sent on `/meter-readings/closing` or included in `/shifts/{id}/end`.

### Other endpoints

```http
GET  /api/v1/fuel-types
GET  /api/v1/fuel-rates
POST /api/v1/fuel-rates

GET  /api/v1/tanks
GET  /api/v1/tanks/{id}
PUT  /api/v1/tanks/{id}
POST /api/v1/tanks/{id}/transactions
GET  /api/v1/tanks/{id}/transactions

GET  /api/v1/pumps
GET  /api/v1/nozzles

GET  /api/v1/shifts
GET  /api/v1/shifts/current
GET  /api/v1/shifts/{id}

GET  /api/v1/meter-readings
GET  /api/v1/sales

GET    /api/v1/customers
POST   /api/v1/customers
GET    /api/v1/customers/{id}
PUT    /api/v1/customers/{id}
DELETE /api/v1/customers/{id}
GET    /api/v1/customers/{id}/ledger
POST   /api/v1/customers/{id}/credit-sales
POST   /api/v1/customers/{id}/payments

GET    /api/v1/expenses
POST   /api/v1/expenses
DELETE /api/v1/expenses/{id}

GET  /api/v1/products
POST /api/v1/products
GET  /api/v1/products/{id}
POST /api/v1/products/{id}/stock
POST /api/v1/products/{id}/sales

GET  /api/v1/reports/profit
GET  /api/v1/alerts
GET  /api/v1/audit-logs
```

Envelope:

```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
```

Paginated lists also include `meta.current_page`, `meta.last_page`, `meta.per_page`, and `meta.total`.

## Architecture

```
app/
  Enums/
  Exceptions/
  Http/
    Controllers/Api/V1/
    Requests/Api/V1/
    Resources/Api/V1/
  Models/
  Repositories/
    Contracts/
    Eloquent/
  Services/
  Support/
```

Controllers stay thin. Form Requests validate input. Services own business rules (rate windows, shift close, stock math, credit limits). Repositories isolate Eloquent queries. API Resources shape JSON.

## Database design summary

| Table | Purpose |
| --- | --- |
| `users` | Attendants/admins (Sanctum tokens in `personal_access_tokens`) |
| `fuel_types` | `petrol`, `diesel` |
| `fuel_rates` | Dated rate windows; one active rate per fuel type |
| `tanks` | One tank per fuel type, plus `capacity`, `current_stock`, `low_level_threshold` |
| `tank_transactions` | `purchase`, `wastage`, `adjustment` |
| `pumps` | Physical units (3 seeded) |
| `nozzles` | Side A/B + fuel type + last closing reading |
| `shifts` | `open` / `closed` |
| `meter_readings` | Opening + closing per nozzle per shift |
| `sales` | Derived at shift close; never keyed in by hand |
| `customers` | Credit accounts |
| `customer_transactions` | `sale` / `payment` ledger |
| `expenses` | `salary`, `utility`, `misc` |
| `products` | Non-fuel inventory master |
| `product_stock` | Purchases and adjustments |
| `product_sales` | Counter sales with profit |
| `audit_logs` | Who changed what, before/after |

All business tables use `deleted_at` (soft deletes). `audit_logs` is append-only.

## Tests

```bash
php artisan test
```

Coverage includes login, shift start/end with derived sales and tank deduction, closed-shift immutability, rate overlap, credit-limit enforcement, and the dashboard contract.

## Future features

- RS485 pump integration
- Tatsuno protocol support
- Real-time pump listener service
- WhatsApp alerts
- Mobile app
- Multi-branch support
- Role-based access control
- Fraud detection (variance alerts)
