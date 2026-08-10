# UPLYFT — Multi-Institute SaaS Platform

UPLYFT is a multi-institute school management, examination, and governance SaaS platform built with PHP and Laravel 11.

---

## 🏛️ Architecture & Module Breakdown

UPLYFT is designed with a modular architecture to support parallel multi-developer workflows across separate system domains:

```
UPLYFT Platform
├── Module 1: Core Architecture & Global Admin Governance (Completed)
│   ├── Multi-Institute Database Schema (`institutes`, `institute_feature_toggles`)
│   ├── Global Admin Dashboard (`/global-admin`)
│   ├── Dynamic Feature Toggle Middleware (`CheckInstituteFeature`)
│   └── Architectural Privacy Scope (`TenantPrivacyScope`)
│
├── Module 2: Authentication, Roles & Automated Tenant Onboarding
├── Module 3: Institute Principal Panel & School Structure Setup (Classes, Subjects, Sections)
├── Module 4: Examination, Grading & Marksheet Engine
├── Module 5: Student & Teacher Portals
└── Module 6: AI Analytics & Learning Management System
```

---

## 🚀 Module 1 (Core Governance Architecture)

### Key Features Implemented

1. **Global Admin Panel (`/global-admin`)**:
   - Platform Overview stats & tenant management
   - Institute registration with Education System selection (`Matric`, `Higher Secondary`, `O/A Level`, `ACCA`, `Other`)
   - Plan Subscription tiers (`Basic`, `Standard`, `Premium`)
   - Custom styled animated deactivation/restoration modal (Soft Deletes)

2. **Dynamic Feature Control (`InstituteFeatureToggle`)**:
   - 12+ per-institute granular feature flags (AI Bot, Online Exams, Payroll, LMS, etc.)
   - Plan tier presets with one-click default application
   - Guard middleware: `Route::middleware('feature:ai_bot')->group(...)`

3. **Data Privacy Guardrail (`TenantPrivacyScope`)**:
   - Restricts Global Admin Eloquent queries to governance-only fields by default.
   - Prevents accidental exposure of operational tenant data unless `GLOBAL_ADMIN_EMERGENCY_OVERRIDE=true` is set.

---

## 🛠️ Local Environment Setup

### Prerequisites
- PHP >= 8.2
- MySQL >= 8.0
- Composer
- Node.js & NPM

### Setup Instructions

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Hexxi07/UPLYFT.git
   cd UPLYFT
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Environment Configuration**:
   Copy `.env.example` to `.env` and update your database credentials:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Migrations & Storage Link**:
   ```bash
   php artisan migrate
   php artisan storage:link
   ```

5. **Start Development Server**:
   ```bash
   php artisan serve
   ```
   Access the Global Admin panel at: `http://localhost:8000/global-admin`

---

## 👥 Multi-Developer Collaboration Guidelines

When building additional modules (e.g. Principal Panel, Student Portal, Exams):

1. **Route Files**: Place module routes in designated files inside `routes/` (e.g. `routes/principal.php`, `routes/student.php`) and register them in `bootstrap/app.php`.
2. **Feature Protection**: Wrap new routes in the `feature:{flag_name}` middleware to respect institute feature toggles.
3. **Database Migrations**: Name migrations cleanly with timestamps. Do not modify existing core migrations (`institutes`, `institute_feature_toggles`).
