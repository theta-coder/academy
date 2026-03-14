# Academy Management System

## Project Overview

A comprehensive **Laravel 12 + Vue 3 + Inertia.js** academy management system with multi-campus branch architecture, role-based access control, and complete fee management functionality.

### Key Features

- **Multi-Campus Support**: Branch-based data scoping throughout the application
- **Fee Management**: Complete fee lifecycle from structure definition to collection, refunds, and reporting
- **Student Management**: Enrollment, concessions, scholarships, installment plans
- **Academic Management**: Classes, sections, subjects, exams, attendance, results
- **Role-Based Access**: Spatie Permission package with roles (Admin, Branch Manager, Staff, etc.)
- **Real-time UI**: Vue 3 with Inertia.js for seamless SPA experience

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.2+, Laravel 12 |
| **Frontend** | Vue 3, Inertia.js, Tailwind CSS |
| **Database** | SQLite (default), MySQL/PostgreSQL compatible |
| **Build Tool** | Vite 7 |
| **Key Packages** | Spatie Permission, Maatwebsite Excel, Laravel DataTables, Ziggy |

### Dependencies

**Backend (composer.json):**
- `laravel/framework` ^12.0
- `inertiajs/inertia-laravel` ^2.0
- `spatie/laravel-permission` ^6.24
- `maatwebsite/excel` ^1.1
- `yajra/laravel-datatables-oracle` ^12.6
- `tightenco/ziggy` ^2.0

**Frontend (package.json):**
- `vue` ^3.4.0
- `@inertiajs/vue3` ^2.3.11
- `@headlessui/vue` ^1.7.23
- `@heroicons/vue` ^2.2.0
- `tailwindcss` ^3.2.1
- `datatables.net` ^2.3.7

---

## Project Structure

```
academy-management/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # 64 controllers (Fee*, Student*, Class*, etc.)
│   │   └── Middleware/
│   ├── Models/               # 48 Eloquent models
│   ├── Providers/
│   └── Services/             # FeeGenerationService, FeePaymentService
├── database/
│   ├── migrations/           # Database schema
│   ├── seeders/
│   └── factories/
├── resources/
│   ├── js/
│   │   ├── Pages/            # Vue pages (Inertia)
│   │   │   ├── FeeVouchers/
│   │   │   ├── FeePayments/
│   │   │   ├── Students/
│   │   │   └── ...
│   │   ├── Components/       # Reusable Vue components
│   │   └── app.js            # Entry point
│   └── views/
│       └── app.blade.php     # SPA entry point
├── routes/
│   └── web.php               # All routes (web + API-style endpoints)
└── config/
```

---

## Building and Running

### Initial Setup

```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Build assets
npm run build
```

### Development

```bash
# Start all dev servers (recommended)
composer run dev

# Or start individually:
php artisan serve              # Laravel dev server (http://localhost:8000)
npm run dev                    # Vite HMR server
php artisan queue:listen       # Queue worker
```

### Production

```bash
# Optimize and build
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

### Testing

```bash
# Run tests
composer run test
# or
php artisan test
```

---

## Database Schema

### Core Tables (56 total)

**Fee Management (24 tables):**
- `fee_types`, `fee_structures`, `fee_vouchers`, `fee_payments`
- `fee_waivers`, `fee_refunds`, `fee_fine_rules`, `fee_voucher_fines`
- `fee_concession_types`, `student_fee_concessions`
- `fee_advance_adjustments`, `fee_approval_requests`, `fee_reminders`
- `fee_structure_change_log`, `fee_voucher_edit_history`
- `voucher_discount_breakdowns`, `fee_collection_summary`
- `installment_plans`, `installment_schedule`, `student_installment_assignments`
- `student_advance_balance`, `student_ledger`, `previous_year_balances`

**Academic (12 tables):**
- `academic_years`, `branches`, `classes`, `sections`, `class_sections`
- `subjects`, `subject_groups`, `class_subjects`, `class_section_subjects`
- `exams`, `exam_subjects`, `exam_results`

**People (8 tables):**
- `students`, `parents`, `teachers`, `users`
- `student_enrollments`, `branch_classes`, `attendance`, `timetables`

**System (12 tables):**
- `users`, `permissions`, `roles`, `role_permissions`
- `cache`, `cache_locks`, `sessions`, `jobs`, `job_batches`, `failed_jobs`
- `migrations`, `password_reset_tokens`, `activity_logs`

---

## Architecture

### Backend Patterns

**Controllers:**
- 64 controllers organized by domain
- Standard RESTful methods: `index`, `create`, `edit`, `store`, `update`, `destroy`
- DataTables support via `getDataTables*()` methods
- Mobile API support via `getMobile*()` methods
- Dropdown endpoints for select options: `dropdown()`

**Models:**
- 48 Eloquent models with relationships
- Soft deletes where applicable
- Proper fillable and casts
- Query scopes (e.g., `scopeActive()`, `scopePending()`)

**Services:**
- `FeeGenerationService` - Monthly voucher generation
- `FeePaymentService` - Payment processing with ledger updates

### Frontend Patterns

**Pages:**
```
resources/js/Pages/
├── FeeVouchers/
│   ├── Index.vue
│   ├── Create.vue
│   └── Edit.vue
├── FeePayments/
├── Students/
└── ...
```

**Components:**
```
resources/js/Components/
├── Common/           # UI primitives
├── Crud/             # CRUD table/form wrappers
├── Forms/            # Form field components
└── Layout/           # App shell, nav, sidebar
```

### Inertia Integration

Controllers return Inertia renders with props:
```php
return Inertia::render('FeeVouchers/Index', [
    'filters' => $filters,
    'stats' => $stats,
]);
```

Vue pages receive props via `defineProps()`:
```vue
<script setup>
defineProps({
    filters: Object,
    stats: Object,
});
</script>
```

---

## Fee Management Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    FEE MANAGEMENT SYSTEM                        │
└─────────────────────────────────────────────────────────────────┘

PHASE 1: CONFIGURATION
══════════════════════════════════════════════════════════════════
FeeType → FeeConcessionType → FeeFineRule → SiblingDiscountRule → InstallmentPlan

PHASE 2: STRUCTURE & ASSIGNMENT
══════════════════════════════════════════════════════════════════
FeeStructure → StudentFeeConcession → StudentInstallmentAssignment

PHASE 3: VOUCHER GENERATION
══════════════════════════════════════════════════════════════════
FeeVoucher → FeeVoucherFine → VoucherDiscountBreakdown → FeeVoucherEditHistory

PHASE 4: PAYMENT COLLECTION
══════════════════════════════════════════════════════════════════
FeePayment → OnlinePaymentProof → ChequeTracking → FeeAdvanceAdjustment

PHASE 5: EXCEPTIONS & APPROVALS
══════════════════════════════════════════════════════════════════
FeeWaiver → FeeRefund → FeeApprovalRequest → FeeReminder

PHASE 6: REPORTING & LEDGER
══════════════════════════════════════════════════════════════════
StudentLedger → FeeCollectionSummary → PreviousYearBalance
```

---

## Development Conventions

### Coding Standards

- **PHP**: PSR-12, enforced by Laravel Pint
- **Vue**: Composition API with `<script setup>`
- **Naming**: 
  - Controllers: `PascalCase` + `Controller` suffix
  - Models: `PascalCase` singular
  - Routes: `kebab-case` plural

### Testing Practices

- Unit tests in `tests/Unit/`
- Feature tests in `tests/Feature/`
- SQLite in-memory database for testing

### Git Workflow

- Main branch: `main`
- Feature branches: `feature/{name}`
- Fix branches: `fix/{name}`

---

## Key Routes

### Fee Management
```php
Route::resource('fee-types', FeeTypeController::class);
Route::resource('fee-structures', FeeStructureController::class);
Route::resource('fee-vouchers', FeeVoucherController::class);
Route::resource('fee-payments', FeePaymentController::class);
Route::resource('fee-waivers', FeeWaiverController::class);
Route::resource('fee-refunds', FeeRefundController::class);
// ... and more
```

### Student Management
```php
Route::resource('students', StudentController::class);
Route::resource('student-enrollments', StudentEnrollmentController::class);
Route::resource('student-fee-concessions', StudentFeeConcessionController::class);
```

### Academic
```php
Route::resource('classes', ClassController::class);
Route::resource('class-sections', ClassSectionController::class);
Route::resource('exams', ExamController::class);
Route::resource('attendance', AttendanceController::class);
```

---

## Common Tasks

### Add New Fee Type
1. Create migration (if needed): `php artisan make:migration add_xxx_to_fee_types`
2. Update model: `app/Models/FeeType.php`
3. Update controller: `app/Http/Controllers/FeeTypeController.php`
4. Create Vue page: `resources/js/Pages/FeeTypes/`

### Add New Controller
```bash
php artisan make:controller XxxController --resource
```

### Run Migrations
```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:refresh --seed
```

### Clear Caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Environment Variables

```env
APP_NAME="Academy Management"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
# Or MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=academy
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## Useful Links

- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js Documentation](https://inertiajs.com/)
- [Vue 3 Documentation](https://vuejs.org/)
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Laravel DataTables](https://yajrab datatable.com/)
