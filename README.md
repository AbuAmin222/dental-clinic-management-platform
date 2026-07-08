![CI/CD Pipeline](https://github.com/username/repo-name/actions/workflows/ci-cd.yml/badge.svg)
![Docker Image Version](https://img.shields.io/badge/docker-ready-blue?logo=docker)
![License](https://img.shields.io/badge/license-MIT-green)

<div align="center">

# 🦷 Dental Clinic Management System

**Enterprise-grade, containerised, multi-role clinic management platform built for the Palestinian healthcare market.**

[![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.x-9553E9?style=flat-square)](https://inertiajs.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Redis](https://img.shields.io/badge/Redis-7-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)
[![CI/CD](https://img.shields.io/badge/GitHub_Actions-CI%2FCD-2088FF?style=flat-square&logo=github-actions&logoColor=white)](https://github.com/features/actions)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)](LICENSE)

---

*Appointments · Dental Records · X-Ray Management · Invoicing · Multi-Gateway Payments*

</div>

---

## Table of Contents

- [Overview](#overview)
- [Core Features](#core-features)
- [System Architecture](#system-architecture)
- [Tech Stack](#tech-stack)
- [Infrastructure Stack](#infrastructure-stack)
- [Prerequisites](#prerequisites)
- [Quick Start — Docker](#quick-start--docker)
- [Local Development — Without Docker](#local-development--without-docker)
- [Environment Configuration](#environment-configuration)
- [Database](#database)
- [CI/CD Pipeline](#cicd-pipeline)
- [User Roles & Permissions](#user-roles--permissions)
- [Payment System](#payment-system)
- [File Storage Architecture](#file-storage-architecture)
- [Security Model](#security-model)
- [Project Structure](#project-structure)
- [Available Scripts](#available-scripts)
- [Known Limitations & Roadmap](#known-limitations--roadmap)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

The **Dental Clinic Management System** is a production-containerised, multi-role web application that digitises the full patient journey — from first registration through appointment booking, clinical record-keeping, invoicing, and digital payment settlement — for dental clinics operating in the Palestinian market.

Built on **Laravel 12**, bridged to **Vue 3** through **Inertia.js**, and deployed as a three-service Docker Compose cluster (**MySQL 8.0 + Redis 7 + PHP-FPM/Nginx**), the system is automated end-to-end with a **GitHub Actions CI/CD pipeline** that runs quality assurance on every pull request and builds and pushes a production container image on every push to the `production` branch.

### Key design decisions

| Decision | Rationale |
|---|---|
| Single `users` table with a `role` enum | Avoids guard proliferation. All role profiles (`doctors`, `patients`, `receptionists`) are satellite tables that extend the base identity |
| Private disk for all sensitive files | National ID scans and X-ray images are stored on Laravel's `local` (non-web-accessible) disk and served through authenticated, policy-gated controller endpoints — never as static file links |
| Strategy pattern for payments | Each gateway is an independent class implementing `PaymentStrategy`. Adding a new gateway requires only one new class and one match arm in `PaymentManager` — nothing else changes |
| ILS as the canonical currency | Every invoice, pricing record, and balance is denominated in Israeli New Shekel (₪). `CurrencyConverter` handles ILS→USD conversion required only for PayPal |
| Supervisor runs three processes in one container | PHP-FPM, Nginx, and the Laravel queue worker all run inside the same `app` container — supervised, auto-restarted, and logging directly to Docker stdout |
| Non-standard Docker ports | `8010:80`, `3310:3306`, `6380:6379` prevent conflicts with any existing local services on default ports |
| Three-layer validation | Frontend wizard → server-side controller validation → model saving observer. Client input is never the final word on a financial value |

---

## Core Features

### 👤 Patient Portal
- Self-registration via a 7-step wizard with per-step live field validation
- Personal medical file: blood group, allergies, chronic diseases, emergency contacts
- Appointment booking with real-time doctor availability and overlap-collision prevention
- Invoice dashboard: outstanding balance, gross bill, cleared cash breakdown
- Digital payment checkout: Bank of Palestine (Visa/Mastercard), Jawwal Pay, PalPay, PayPal (with ILS→USD conversion)
- Dental record history: per-tooth condition log, policy-gated X-ray viewer

### 🩺 Doctor Portal
- Today's appointment schedule with patient details and visit reasons
- Create dental records per appointment: tooth number, condition type, description, X-ray upload (private disk)
- Full patient clinical history across all appointments and records
- Personal service pricing catalog in ₪ ILS: add, edit, delete billable services
- TOTP two-factor authentication (Google Authenticator-compatible)

### 🗄️ Receptionist Portal
- Patient registry: register new patients, search by name / identity number / phone
- Appointment management: book, confirm, cancel, mark no-show; server-side overlap-collision prevention on both patient and receptionist booking flows
- Invoice lifecycle: create from appointment, attach doctor pricing, set due date, update, delete
- Dashboard counters: active appointments, unpaid invoices, total registered patients

### 🔧 Admin *(in development)*
- Full application configuration, user management, department/specialization CRUD, system reporting

---

## System Architecture

```
┌──────────────────────────────────────────────────────────────────┐
│                       Browser (Vue 3 SPA)                        │
│    Inertia.js pages · Composables · SweetAlert2 · Ziggy routes   │
└─────────────────────────────┬────────────────────────────────────┘
                              │ HTTP / Inertia protocol / axios JSON
                              ▼
┌──────────────────────────────────────────────────────────────────┐
│                  Docker: app container                            │
│                                                                  │
│  ┌──────────────────┐   ┌───────────────────────────────────┐   │
│  │  Nginx (port 80) │   │  Supervisor (process manager)     │   │
│  │  try_files SPA   │──▶│  ├─ php-fpm  (autorestart)       │   │
│  │  max_body 20MB   │   │  ├─ nginx    (autorestart)        │   │
│  │  FastCGI :9000   │   │  └─ queue:work redis (tries=3)    │   │
│  └──────────────────┘   └───────────────────────────────────┘   │
│                                                                  │
│  PHP 8.3-fpm-alpine + OPcache (256MB, 20k files, no timestamps) │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Laravel 12 Application                                   │   │
│  │                                                           │   │
│  │  Fortify (Auth/2FA) · Jetstream (Profile) · Sanctum      │   │
│  │           ↓                                               │   │
│  │  CheckRole Middleware (role:doctor/patient/receptionist)  │   │
│  │           ↓                                               │   │
│  │  Controllers (Doctor/ · Patient/ · Receptionist/)         │   │
│  │           ↓                                               │   │
│  │  Policies (Appointment · DentalRecord · Invoice · Pricing)│   │
│  │           ↓                                               │   │
│  │  Eloquent Models (11 tables)                              │   │
│  │           ↓                                               │   │
│  │  Payment Strategy Layer                                   │   │
│  │  (BOP · JawwalPay · PalPay · PayPal + CurrencyConverter) │   │
│  │           ↓                                               │   │
│  │  FileStorageService (public disk · local private disk)    │   │
│  └──────────────────────────────────────────────────────────┘   │
└───────────────────────────  │  ────────────────────────────-─────┘
                              │
              ┌───────────────┴────────────────┐
              ▼                                ▼
┌─────────────────────────┐      ┌─────────────────────────┐
│  Docker: mysql           │      │  Docker: redis           │
│  MySQL 8.0               │      │  Redis 7-alpine          │
│  port 3310:3306          │      │  port 6380:6379          │
│  volume: dental-mysql-   │      │  volume: dental-redis-   │
│          data            │      │          data            │
│  healthcheck: mysqladmin │      │  healthcheck: redis-cli  │
└─────────────────────────┘      └─────────────────────────┘
         ↑ session / cache / queue via Redis
         ↑ primary relational data via MySQL
```

---

## Tech Stack

### Backend
| Package | Version | Purpose |
|---|---|---|
| PHP | `8.3` (Docker runtime) | Language runtime |
| Laravel Framework | `^12.0` | Application framework |
| Laravel Jetstream | `^5.4` | Auth scaffolding — Inertia stack, Teams disabled |
| Laravel Fortify | `^1.33` | Authentication action layer (login, 2FA, registration) |
| Laravel Sanctum | `^4.0` | Session + API token guard |
| Laravel Tinker | `^2.10` | Interactive REPL |
| Inertia.js (Laravel adapter) | `^2.0` | Server-side SPA bridge |
| Tightenco Ziggy | `^2.0` | Generates `route()` helper for Vue |

### Frontend
| Package | Version | Purpose |
|---|---|---|
| Vue 3 | `^3.3` | UI component framework |
| Inertia.js (Vue 3 adapter) | `^2.0` | Client-side SPA protocol |
| Tailwind CSS | `^3.4` | Utility-first CSS with JIT |
| `@tailwindcss/forms` | `^0.5` | Form element base styles |
| `@tailwindcss/typography` | `^0.5` | Prose rendering (Privacy Policy, Terms) |
| Vite | `^5.0` | Asset bundler with HMR |
| SweetAlert2 | `^11` | Notifications and confirmation dialogs |
| Axios | `^1.11` | HTTP client for payment JSON API |

### Development only
| Package | Purpose |
|---|---|
| `barryvdh/laravel-ide-helper` | PHPDoc generation for Eloquent models and Facades |
| Laravel Pail | Real-time log viewer in terminal |
| Laravel Pint | PHP code style fixer (PSR-12, zero-config) |
| Laravel Sail | Docker wrapper for local development |
| PHPUnit `^11` | Test runner |
| Faker PHP | Seed data generation |
| Mockery | Object mocking for tests |
| NunoMaduro/Collision | Better error output in terminal |

---

## Infrastructure Stack

### Docker Compose services

| Service | Image | Exposed port | Internal port | Health check |
|---|---|---|---|---|
| `app` | `php:8.3-fpm-alpine` | `8010` | `80` | — |
| `mysql` | `mysql:8.0` | `3310` | `3306` | `mysqladmin ping` every 5s |
| `redis` | `redis:7-alpine` | `6380` | `6379` | `redis-cli ping` every 5s |

The `app` service waits for both `mysql` and `redis` to pass their health checks before starting. All three containers persist data via named volumes.

| Volume | Used by | Contents |
|---|---|---|
| `dental-storage-data` | `app` | Laravel `storage/` (uploads, X-rays, logs) |
| `dental-mysql-data` | `mysql` | Database data directory |
| `dental-redis-data` | `redis` | Redis snapshot persistence |

### Dockerfile — 3-stage build

```
Stage 1  backend-vendor-builder   composer:2.7
         └── composer install --no-dev --optimize-autoloader
             (no scripts, prefer-dist — fastest possible install)

Stage 2  frontend-assets-builder  node:20-alpine
         └── npm ci + npm run build
             (vendor/ copied from Stage 1 for Ziggy route generation)

Stage 3  runtime                  php:8.3-fpm-alpine
         ├── Tools: nginx, supervisor, curl, zip, git, bash
         ├── PHP extensions via mlocati/docker-php-extension-installer:
         │   pdo_mysql, gd, zip, opcache, redis (PECL)
         ├── Config files from docker/:
         │   nginx.conf → /etc/nginx/nginx.conf
         │   opcache.ini → /usr/local/etc/php/conf.d/opcache.ini
         │   supervisord.conf → /etc/supervisor/conf.d/supervisord.conf
         └── CMD: supervisord (manages nginx + php-fpm + queue worker)
```

### Nginx configuration (`docker/nginx.conf`)

| Setting | Value | Purpose |
|---|---|---|
| `worker_processes` | `auto` | Matches available CPU cores |
| `worker_connections` | `1024` | Max concurrent connections per worker |
| `client_max_body_size` | `20M` | Handles X-ray uploads (server limit matches form 4MB + overhead) |
| `keepalive_timeout` | `65s` | Keep TCP connections alive for SPA navigation |
| `root` | `/var/www/public` | Laravel public directory |
| `try_files $uri /index.php` | SPA fallback | All unknown paths route to `index.php` (Inertia requirement) |
| `fastcgi_pass` | `127.0.0.1:9000` | PHP-FPM on same container |
| `deny all` | hidden files | Blocks `.env`, `.git`, `.htaccess` etc. except `.well-known` |

### Supervisor configuration (`docker/supervisord.conf`)

Supervisor runs three managed processes, each with `autorestart=true` and logs piped directly to Docker stdout/stderr (no log file accumulation inside the container):

| Program | Command | Purpose |
|---|---|---|
| `php-fpm` | `php-fpm -F` (foreground) | PHP request handler |
| `nginx` | `nginx -g "daemon off;"` | HTTP server |
| `laravel-worker` | `php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600` | Background job processor |

The queue worker uses the `redis` connection, retries failed jobs up to 3 times, and recycles itself every 3600 seconds to prevent memory leaks.

### OPcache (`docker/opcache.ini`)

```ini
[opcache]
opcache.enable                = 1
opcache.enable_cli            = 1      ; Also active in CLI (artisan commands)
opcache.memory_consumption    = 256    ; MB — holds compiled bytecode in shared memory
opcache.interned_strings_buffer = 16   ; MB — interned string deduplication
opcache.max_accelerated_files = 20000  ; Max files cached (covers all vendor + app)
opcache.validate_timestamps   = 0      ; NEVER re-reads files — maximum performance
opcache.revalidate_freq       = 0      ; Irrelevant when validate_timestamps=0
opcache.fast_shutdown         = 1      ; Faster per-request shutdown
```

> `validate_timestamps=0` means code changes inside the container are never picked up without a restart. This is correct for production (immutable containers); never use this setting in local development.

---

## Prerequisites

### For Docker deployment (recommended)
- **Docker Desktop** `>= 4.x`
- **Git**

### For local development (without Docker)
- **PHP** `>= 8.2` with extensions: `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `tokenizer`, `xml`, `bcmath`, `fileinfo`
- **Composer** `>= 2.0`
- **Node.js** `>= 18.x` + **npm** `>= 9.x`
- **MySQL** `>= 8.0` or **MariaDB** `>= 10.6`
- **Git**

---

## Quick Start — Docker

```bash
# 1. Clone the repository
git clone https://github.com/your-org/dental-clinic.git
cd dental-clinic

# 2. Set up environment
cp .env.example .env
# Edit .env — set DB_PASSWORD and DB_ROOT_PASSWORD at minimum

# 3. Build and start all three services
#    (app waits for mysql + redis health checks before starting)
docker compose up --build -d

# 4. Run database migrations
docker compose exec app php artisan migrate

# 5. Create the public storage symlink
docker compose exec app php artisan storage:link

# 6. Open the application
#    http://localhost:8010
```

### Essential Docker commands

```bash
# Start / stop
docker compose up -d                          # Start in background
docker compose down                           # Stop and remove containers
docker compose down -v                        # Stop + remove volumes (DATA LOSS — dev only)

# Application management
docker compose exec app php artisan migrate           # Run migrations
docker compose exec app php artisan migrate:fresh     # Drop all + re-migrate (dev only)
docker compose exec app php artisan optimize:clear    # Clear all cached files
docker compose exec app php artisan storage:link      # Create public/storage symlink
docker compose exec app php artisan tinker            # Interactive REPL

# Monitoring
docker compose logs -f app                    # Follow application logs
docker compose logs -f mysql                  # Follow database logs
docker compose ps                             # Check health of all services
```

### Required `.env` variables for Docker

```dotenv
DB_DATABASE=dental_clinic
DB_USERNAME=clinic_admin
DB_PASSWORD=your-strong-password          # Required — no default
DB_ROOT_PASSWORD=your-root-password       # Required — MySQL root password
```

The `docker-compose.yml` reads these via `env_file: .env` and injects them into the MySQL container. The `app` container overrides `DB_HOST=mysql` and `DB_PORT=3306` automatically.

---

## Local Development — Without Docker

```bash
# 1. Clone
git clone https://github.com/your-org/dental-clinic.git
cd dental-clinic

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate
# Edit .env: set DB_CONNECTION=mysql, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Migrate and link storage
php artisan migrate
php artisan storage:link

# 5. Start all dev processes concurrently
composer run dev
```

`composer run dev` starts four processes via `concurrently`:

| Label | Command | Colour |
|---|---|---|
| `server` | `php artisan serve` | Blue |
| `queue` | `php artisan queue:listen --tries=1 --timeout=0` | Purple |
| `logs` | `php artisan pail --timeout=0` | Pink |
| `vite` | `npm run dev` | Orange |

---

## Environment Configuration

### `.env` — local development

```dotenv
APP_NAME=DentalClinicApplication
APP_ENV=local
APP_KEY=                              # php artisan key:generate
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_LOCALE=en
APP_FALLBACK_LOCALE=en

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database (MySQL — local)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dental_clinic
DB_USERNAME=root
DB_PASSWORD=

# Session & Queue (database driver locally)
SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
CACHE_STORE=database

# Redis (optional locally — required in Docker)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password       # Google App Password, not your account password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

# PayPal currency conversion (ILS → USD)
# Add to config/services.php:
# 'exchange' => ['ils_to_usd' => env('ILS_TO_USD_RATE', 0.27)]
ILS_TO_USD_RATE=0.27

VITE_APP_NAME="${APP_NAME}"
```

### `.env` — Docker / production overrides

The `docker-compose.yml` automatically overrides the following:

```yaml
DB_CONNECTION: mysql
DB_HOST: mysql          # Docker service name — not 127.0.0.1
DB_PORT: 3306
CACHE_STORE: redis
QUEUE_CONNECTION: redis
SESSION_DRIVER: redis   # Sessions in Redis for performance
APP_ENV: production
APP_DEBUG: "false"
```

### Required `config/services.php` addition

```php
'exchange' => [
    'ils_to_usd' => env('ILS_TO_USD_RATE', 0.27),
],
```

This is read by `CurrencyConverter` when processing PayPal payments. Replace `0.27` with a live rate or integrate an FX API for production.

---

## Database

### Engine

**MySQL 8.0** — both locally and in Docker. All migrations are written for MySQL. The CI/CD pipeline also tests against MySQL 8.0.

### Schema overview (11 tables)

```
users  (base identity — all roles share this table, role enum: admin/doctor/patient/receptionist)
 ├── doctors          1:1   specialization_id → specializations
 │   ├── appointments 1:N
 │   ├── dental_records 1:N  (SoftDeletes; X-ray auto-deleted on force-delete)
 │   ├── pricings     1:N   (billable service catalog, amounts in ILS)
 │   └── invoices     1:N
 │        └── payment_transactions 1:N  (per-gateway charge records)
 ├── patients         1:1
 │   ├── appointments 1:N
 │   ├── dental_records 1:N
 │   └── invoices     1:N
 └── receptionists    1:1   department_id → departments

departments    (organisational units)
specializations (medical specialties)
```

### Migrations

```bash
php artisan migrate                   # Run all pending migrations
php artisan migrate:fresh             # Drop all + re-run (dev only)
php artisan migrate:fresh --seed      # Drop + re-run + seed data
php artisan migrate:rollback          # Roll back last batch
```

### Create admin account

```bash
php artisan tinker
# or inside Docker:
docker compose exec app php artisan tinker
```

```php
\App\Models\User::create([
    'first_name'      => 'Admin',
    'middle_name'     => 'Clinic',
    'last_name'       => 'System',
    'email'           => 'admin@dentalclinic.ps',
    'identity_number' => '000000001',
    'phone'           => '0591234567',
    'password'        => bcrypt('your-secure-password'),
    'role'            => 'admin',
    'gender'          => 'Male',
    'date_of_birth'   => '1990-01-01',
    'is_active'       => true,
]);
```

---

## CI/CD Pipeline

The project ships with a full **GitHub Actions** pipeline defined at `.github/workflows/deploy.yml`.

### Trigger conditions

| Event | Branches | Jobs triggered |
|---|---|---|
| `push` | `main`, `production` | `quality-assurance` + (if `production`) `build-and-ship` |
| `pull_request` | `main` | `quality-assurance` only |

### Job 1: `quality-assurance` — Code Auditing & Testing

Runs on every push and every pull request to `main`.

```
Environment: ubuntu-latest + MySQL 8.0 service + PHP 8.3 + Node 20

Steps:
  1. Checkout source code
  2. Set up PHP 8.3 (extensions: pdo_mysql, gd, zip | tools: composer v2)
  3. Set up Node.js 20 (with npm cache)
  4. Restore Composer cache (keyed on composer.lock hash)
  5. composer install --prefer-dist --no-progress
  6. npm ci + npm run build
  7. cp .env.example .env + php artisan key:generate
  8. php artisan test --parallel
     (test DB: MySQL clinic_test | cache/session: array | queue: sync)
```

### Job 2: `build-and-ship` — Production Image

Runs **only** on push to the `production` branch, after `quality-assurance` passes.

```
Steps:
  1. Checkout source code
  2. Set up Docker Buildx
  3. Authenticate to GitHub Container Registry (ghcr.io)
  4. docker/build-push-action:
     - Tags: clinic-app:latest + clinic-app:{git-sha}
     - Layer cache: GitHub Actions Cache (type=gha, mode=max)
     - Pushes to: ghcr.io/{owner}/{repo}/clinic-app
```

The resulting image uses the exact same 3-stage `Dockerfile` as local `docker compose up --build`.

### Deploying a new version

```bash
# 1. Merge your feature branch into main (runs QA)
git checkout main
git merge feature/your-feature
git push

# 2. Promote to production (runs QA + builds + pushes image)
git checkout production
git merge main
git push

# 3. On your server — pull and restart
docker compose pull
docker compose up -d
docker compose exec app php artisan migrate --force
```

---

## User Roles & Permissions

### Post-login routing

| Role | Route | URL prefix |
|---|---|---|
| `patient` | `patient.dashboard` | `/patient/` |
| `doctor` | `doctor.dashboard` | `/doctor/` |
| `receptionist` | `receptionist.dashboard` | `/receptionist/` |
| `admin` | `admin.dashboard` *(in development)* | `/admin/` |

Role routing is enforced in two places: `LoginResponse.php` (post-login redirect) and `CheckRole` middleware (every request inside a role-prefixed group).

### Policy matrix

| Resource | Patient | Doctor | Receptionist | Admin |
|---|---|---|---|---|
| Appointment — view | Own only | Own only | All | All |
| Appointment — create | ✓ | — | ✓ | ✓ |
| Appointment — update status | — | Own only | All | All |
| DentalRecord — view | Own only | Own only | — | All |
| DentalRecord — create/update | — | Own only | — | All |
| Invoice — view | Own only | Own only | All | All |
| Invoice — **pay** | Own only | — | — | — |
| Pricing — update/delete | — | Own only | — | All |
| X-ray image — stream | Own only | Own only | — | All |

### Self-registration

`/register` creates **patient accounts only**. Doctor and receptionist accounts are provisioned by an administrator or receptionist from within the authenticated application — never via open self-service signup.

---

## Payment System

The payment layer uses the **Strategy pattern**. Every gateway implements the same `PaymentStrategy` interface with a single `initializePayment(Invoice $invoice, float $amountIls): array` method.

```
PaymentManager::make($method)
  ├── 'visa' | 'mastercard'  →  BankOfPalestineCardService  (ILS, tx prefix: BOP_)
  ├── 'jawwal_pay'           →  JawwalPayService             (ILS, tx prefix: JWP_)
  ├── 'palpay'               →  PalPayService                (ILS, tx prefix: PAL_)
  └── 'paypal'               →  PayPalService    (ILS→USD via CurrencyConverter, prefix: PAYPAL_)
```

### Payment flow

```
1. Patient selects gateway on InvoicePayment.vue
2. axios POST /patient/invoices/{invoice}/pay
   → PatientInvoicePaymentController::process()
       → $this->authorize('pay', $invoice)      ← own patient only (InvoicePolicy)
       → validate: payment_method ∈ [visa, mastercard, jawwal_pay, palpay, paypal]
       → validate: 0.01 ≤ amount ≤ invoice.balance_amount   ← server-side re-check
       → PaymentManager::make($method)->initializePayment($invoice, $amount)
           → PaymentTransaction created {status: pending}
           → Returns {redirect_url, transaction_id}
   → JSON response → Vue redirects browser to sandbox gateway

3. Patient clicks "Confirm" on sandbox gateway page
4. GET /patient/payment/callback/{gateway}/{tx}
   → PatientInvoicePaymentController::callback()
       → PaymentTransaction::where('transaction_id', $tx)->firstOrFail()
       → $this->authorize('pay', $invoice)
       → idempotency check: skip if already completed
       → DB::transaction {
             PaymentTransaction → status: completed
             $amountInIls = (currency === 'ILS') ? amount : CurrencyConverter::usdToIls(amount)
             $invoice->pay($amountInIls)
               → paid_amount += amountInIls
               → status auto-calculated (unpaid/partially_paid/paid)
               → saving observer: balance_amount = max(0, total - paid)
         }
5. redirect → patient.dashboard with success flash
```

### Currency conversion (PayPal only)

```
ILS balance_amount
  → CurrencyConverter::ilsToUsd()   →  USD amount charged via PayPal
  → stored in PaymentTransaction.amount with currency='USD'

On callback:
  → CurrencyConverter::usdToIls()   →  ILS amount credited to Invoice::pay()
```

Rate is read from `config('services.exchange.ils_to_usd')`, driven by `ILS_TO_USD_RATE` in `.env`.

### Adding a new gateway (3 steps total)

1. Create `app/Services/Payment/MyGatewayService.php` implementing `PaymentStrategy`
2. Add one match arm to `PaymentManager::make()`
3. Add the option to `InvoicePayment.vue` + `Rule::in([..., 'my_gateway'])` in the controller

Nothing else changes.

> **Current status:** All gateways redirect to `Payment/Sandbox` (in-app simulator via Inertia). Real API integration required before production.

---

## File Storage Architecture

```
storage/app/
├── public/                        ← symlinked → public/storage/
│   └── uploads/
│       ├── doctor/profiles/       ← doctor photos           (public URL ✓)
│       ├── patient/profiles/      ← patient photos          (public URL ✓)
│       └── receptionist/profiles/ ← receptionist photos     (public URL ✓)
└── [local disk — NOT web-accessible]
    ├── secure/
    │   ├── doctor/identities/     ← national ID scans       (private ✓)
    │   ├── patient/identities/    ← national ID scans       (private ✓)
    │   └── receptionist/identities/
    └── xrays/                     ← dental X-ray images     (private ✓)
```

Private files are served only through authenticated, policy-checked controller methods:

```php
// DentalRecordImageController::show() — X-rays
$this->authorize('view', $dentalRecord);
return Storage::disk('local')->response($dentalRecord->xray_image_path);
```

### `storage_engine()` helper

Auto-loaded via `autoload.files` in `composer.json`. Returns the `FileStorageService` singleton everywhere:

```php
storage_engine()->upload($name, $file, 'xrays', 'local');         // upload
storage_engine()->url($path, 'public', $fallback);                // public URL
storage_engine()->delete($path, 'local');                         // safe delete
storage_engine()->update($name, $newFile, $oldPath, 'dir');       // swap file
```

Filenames are generated as `{slug(name)}-{uuid}.{ext}` — no user-supplied characters reach the filesystem.

---

## Security Model

### Authentication
- Session-based via Laravel Sanctum (`auth:sanctum` guard)
- TOTP two-factor authentication (`TwoFactorAuthenticatable` + Fortify)
- Login: **5 attempts per minute** per `username|IP` (rate limited)
- 2FA challenge: **5 per minute** per session
- Passwords hashed with bcrypt at **12 rounds** (`BCRYPT_ROUNDS=12`)

### Defence in depth

```
Layer 1 — Route middleware: role:doctor / role:patient / role:receptionist
          Wrong-role requests blocked before any controller is reached

Layer 2 — Policy checks: $this->authorize('ability', $model)
          Every resource action verifies ownership before reading or writing

Layer 3 — Financial re-validation on every money endpoint
          'amount' => ['max:' . $invoice->balance_amount]
          Amount can never exceed the outstanding balance, regardless of client input

Layer 4 — Model saving observer
          Invoice::balance_amount = max(0, total_amount - paid_amount)
          Balance is always recomputed before any save — never drifts from the source of truth
```

### Sensitive data matrix

| Data | Storage | Serialisation |
|---|---|---|
| Passwords | bcrypt 12 rounds | Never (`$hidden`) |
| 2FA secret | Encrypted at rest | Never (`$hidden`) |
| 2FA recovery codes | Encrypted at rest | Never (`$hidden`) |
| National ID scans | `local` disk (private) | Paths hidden; served via auth route |
| X-ray images | `local` disk (private) | Paths hidden; served via policy-gated route |
| Profile photos | `public` disk | Standard storage URL |

### Production security checklist

- [ ] `APP_DEBUG=false` (enforced in docker-compose)
- [ ] `APP_ENV=production` (enforced in docker-compose)
- [ ] HTTPS enforced at reverse proxy level; `SESSION_SECURE_COOKIE=true`
- [ ] Add `SoftDeletes` trait to `User` model (prevents cascade wipe on account deletion)
- [ ] Implement `MustVerifyEmail` on `User` model
- [ ] Restrict `/register` to `patient` role only (enforced; verify periodically)
- [ ] Replace `ILS_TO_USD_RATE` static value with live FX API
- [ ] Replace sandbox gateways with real BOP/Jawwal/PalPay/PayPal integrations
- [ ] Review `MAIL_PASSWORD` — use an app-specific password, not an account password
- [ ] Drop dead `current_team_id` column from `users`
- [ ] Fix `dental__records` double-underscore typo in migration `down()`

---

## Project Structure

```
dental-clinic/
├── .github/
│   └── workflows/
│       └── deploy.yml                   ← GitHub Actions CI/CD pipeline
│
├── docker/
│   ├── nginx.conf                       ← Nginx: SPA routes, 20MB upload, FastCGI
│   ├── opcache.ini                      ← OPcache: 256MB, 20k files, no timestamp check
│   └── supervisord.conf                 ← Supervisor: php-fpm + nginx + queue worker
│
├── app/
│   ├── Actions/
│   │   ├── Fortify/
│   │   │   ├── CreateNewUser.php        ← 7-step multi-role registration
│   │   │   ├── UpdateUserProfileInformation.php
│   │   │   ├── UpdateUserPassword.php
│   │   │   └── ResetUserPassword.php
│   │   └── Jetstream/
│   │       └── DeleteUser.php           ← Photo cleanup + soft-delete
│   ├── Helpers/
│   │   └── storage.php                  ← storage_engine() global helper
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Doctor/                  ← Dashboard, DentalRecord, PatientHistory, Pricing
│   │   │   ├── Patient/                 ← PatientController, PaymentController, Sandbox
│   │   │   ├── Receptionist/            ← Appointment, Invoice, Patient, Dashboard
│   │   │   ├── DashboardsController.php ← Role-based login redirect hub
│   │   │   └── DentalRecordImageController.php ← Policy-gated X-ray streaming
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php            ← role:doctor / role:patient / etc.
│   │   │   └── HandleInertiaRequests.php ← Flash messages + shared Inertia props
│   │   └── Responses/
│   │       └── LoginResponse.php        ← Role-aware post-login redirect
│   ├── Models/                          ← 11 Eloquent models with relationships
│   ├── Policies/                        ← AppointmentPolicy, DentalRecordPolicy,
│   │                                       InvoicePolicy (+ 'pay' ability), PricingPolicy
│   ├── Providers/
│   │   ├── AppServiceProvider.php       ← Fortify register view
│   │   ├── FortifyServiceProvider.php   ← Auth actions + rate limiters
│   │   └── JetstreamServiceProvider.php ← DeleteUser + API permissions
│   └── Services/
│       ├── FileStorageService.php       ← Centralised file I/O (upload, url, delete, update)
│       └── Payment/
│           ├── PaymentStrategy.php      ← Interface: initializePayment()
│           ├── PaymentManager.php       ← Strategy resolver (match on method string)
│           ├── CurrencyConverter.php    ← ILS ↔ USD (config-driven, swappable)
│           ├── BankOfPalestineCardService.php
│           ├── JawwalPayService.php
│           ├── PalPayService.php
│           └── PayPalService.php
│
├── database/
│   └── migrations/                      ← 11 migration files (users → payment_transactions)
│
├── resources/
│   └── js/
│       ├── Composables/
│       │   ├── Auth/
│       │   │   ├── useRegisterForm.js   ← 7-step wizard state machine
│       │   │   └── useFileHandle.js     ← Drag-drop, preview, progress simulation
│       │   └── UI/
│       │       └── useNotifications.js  ← SweetAlert2 (notify, toast, confirm, timer)
│       ├── Layouts/
│       │   └── AppLayout.vue            ← Authenticated shell, role-aware nav
│       ├── Pages/
│       │   ├── Auth/                    ← Login, Register, 2FA, Password reset
│       │   ├── Doctor/                  ← Dashboard, DentalRecords, Patients, Pricings
│       │   ├── Patient/                 ← Dashboard, AppointmentCreate, InvoicePayment
│       │   ├── Receptionist/            ← Dashboard, Patients, Appointments, Invoices
│       │   ├── Payment/Sandbox.vue      ← Development gateway simulator (Inertia)
│       │   ├── Profile/Show.vue         ← Jetstream profile page
│       │   └── Profile/Partials/        ← 5 profile sub-forms
│       └── Utils/
│           ├── Auth/dataShapes.js       ← BASE_FIELDS, ROLE_FIELDS, FILE_FIELDS
│           ├── Auth/validationRules.js  ← Per-step validation (all role rules)
│           └── Helpers/formatters.js   ← formatCurrency(ILS), debounce, calculateAge
│
├── routes/
│   ├── web.php                          ← All routes (role-prefixed, policy-guarded)
│   └── api.php                          ← Sanctum /user endpoint
│
├── Dockerfile                           ← 3-stage production build
├── docker-compose.yml                   ← app + mysql + redis cluster
├── .env.example                         ← Template — copy to .env
├── .editorconfig                        ← UTF-8, LF, 4-space indent
├── .gitattributes                       ← LF normalisation, diff drivers
├── .gitignore                           ← Excludes vendor/, node_modules/, .env, build/
├── composer.json                        ← PHP dependencies + Composer scripts
├── package.json                         ← Node dependencies
├── vite.config.js                       ← Vite + Vue plugin + @ alias
├── tailwind.config.js                   ← Tailwind v3 + forms + typography
├── postcss.config.js                    ← Tailwind + Autoprefixer
└── jsconfig.json                        ← @ alias for IDE path resolution
```

---

## Available Scripts

### PHP / Artisan

```bash
composer run setup           # Full install: deps + key + migrate + npm + build
composer run dev             # 4-process dev server (serve + queue + pail + vite)
composer run test            # Clear config cache + run PHPUnit

php artisan migrate          # Run pending migrations
php artisan migrate:fresh    # Drop all + re-run (dev only)
php artisan storage:link     # Create public/storage → storage/app/public symlink
php artisan optimize:clear   # Clear routes, config, views, events cache
php artisan tinker           # Interactive REPL
```

### Docker

```bash
docker compose up --build -d                           # Build + start all services
docker compose down                                    # Stop all services
docker compose exec app php artisan migrate            # Run migrations in container
docker compose exec app php artisan optimize:clear     # Clear cache in container
docker compose exec app php artisan tinker             # Tinker inside container
docker compose logs -f app                             # Follow app logs
docker compose logs -f mysql                           # Follow MySQL logs
docker compose ps                                      # Health status of all services
```

### Node / Vite

```bash
npm run dev     # Vite HMR dev server
npm run build   # Production asset build
```

### Code quality

```bash
./vendor/bin/pint                        # Fix all PHP code style issues (PSR-12)
php artisan test --parallel              # Run full PHPUnit suite in parallel
php artisan ide-helper:generate          # Regenerate IDE Facade helper
php artisan ide-helper:models --nowrite  # Add model PHPDoc (review output before commit)
```

---

## Known Limitations & Roadmap

### Payment gateways — sandboxed

All four gateways redirect to the in-app `Payment/Sandbox.vue` simulator. Real production integration requires:

| Gateway | Requirement |
|---|---|
| Bank of Palestine | BOP merchant credentials + API integration |
| Jawwal Pay | JawwalPay merchant account + HMAC signature verification |
| PalPay | PalPay API key + webhook signature |
| PayPal | PayPal SDK + live client credentials + live FX rate |

### Schema cleanup items

| Item | Fix |
|---|---|
| Dead `current_team_id` on `users` (Teams disabled) | Drop in cleanup migration |
| `dental__records` double-underscore typo in `down()` | Fix `dropIfExists('dental__records')` → `dental_records` |
| No `unique()` on `user_id` in role tables | Add `->unique()` to prevent one user → two doctor rows |
| `SoftDeletes` trait missing from `User` model | Add `use SoftDeletes` to prevent hard-delete cascade |

### Planned features

- [ ] Admin dashboard: user CRUD, department/specialization management, revenue reports
- [ ] Real gateway integrations (BOP, Jawwal, PalPay, PayPal)
- [ ] Live FX rate integration for PayPal ILS→USD
- [ ] `MustVerifyEmail` implementation on `User` model
- [ ] Email notifications: appointment reminders, invoice due-date alerts
- [ ] PDF invoice export (`barryvdh/laravel-dompdf`)
- [ ] Appointment calendar view (FullCalendar)
- [ ] Arabic UI localisation (`APP_LOCALE=ar`)
- [ ] Automated test coverage (Feature + Unit)
- [ ] API resource layer for potential mobile application

---

## Contributing

1. **Branch naming:** `feature/short-description` · `fix/short-description` · `chore/short-description`
2. **Code style:** `./vendor/bin/pint` must pass with zero changes before any commit
3. **Editor:** Follow `.editorconfig` — UTF-8, LF line endings, 4-space indent (2-space for YAML)
4. **Pull requests target `main`:** CI runs QA automatically. Merge to `production` only after QA passes on `main`.
5. **Validation:** Every new endpoint needs both client-side (`validationRules.js`) and server-side (`$request->validate()`) validation
6. **Policies:** Every new controller action touching a model must call `$this->authorize()` — no inline `abort(403)` patterns in new code
7. **Currency:** Store and compute everything in ILS. Use `CurrencyConverter` only at the payment gateway boundary. Never write USD into the `invoices` table.
8. **File storage:** Never build a public URL for a file on the `local` disk. All private files must go through an authenticated, policy-gated controller response.

---

## License

This project is open-source software licensed under the **MIT License**. See [LICENSE](LICENSE) for the full text, third-party notices, and medical software disclaimer.

---

<div align="center">

Built with ❤️ for dental clinics in Palestine

**Laravel 12 · Vue 3 · Inertia.js · Tailwind CSS · MySQL 8 · Redis 7 · Docker · GitHub Actions**

</div>
