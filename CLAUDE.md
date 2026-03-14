# Academy Management System

## Project Overview

Laravel 12 + Vue 3 + Inertia.js academy management system supporting multi-campus branch architecture with role-based access control.

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 12
- **Frontend:** Vue 3, Inertia.js, Tailwind CSS, Vite
- **Database:** SQLite (default), compatible with MySQL/PostgreSQL
- **Key Packages:**
  - Spatie Permission — role/permission management
  - Maatwebsite Excel — import/export
  - Laravel DataTables — server-side table rendering

## Dev Commands

```bash
php artisan serve        # Start local dev server
npm run dev              # Start Vite HMR dev server
php artisan migrate      # Run database migrations
php artisan db:seed      # Seed the database
```

## Build & Test

```bash
npm run build            # Production build
php artisan test         # Run test suite
```

## Conventions

### Backend

- Controllers in `app/Http/Controllers/` — 64 controllers, organized by domain (e.g., `FeePaymentController`, `StudentController`)
- Models in `app/Models/` — 55 Eloquent models
- RESTful routes defined in `routes/web.php`; API dropdown/select endpoints also live in `routes/web.php` (not a separate API file)
- Role-based access enforced via Spatie Permission middleware and policies

### Frontend

- Vue pages at `resources/js/Pages/{ResourceName}/{Action}.vue` (e.g., `Pages/Student/Index.vue`, `Pages/Student/Create.vue`)
- Vue components organized under `resources/js/Components/`:
  - `Common/` — shared UI primitives
  - `Crud/` — reusable CRUD table/form wrappers
  - `Forms/` — form field components
  - `Layout/` — app shell, nav, sidebar
- Inertia.js is the sole bridge between backend and frontend — controllers return `Inertia::render()` with props
- No Blade templates except `resources/views/app.blade.php` (the SPA entry point)

### Architecture

- **Multi-campus:** Branch-based data scoping throughout the application
- **Auth:** Laravel Breeze/Sanctum session auth + Spatie roles (Admin, Branch Manager, Staff, etc.)
