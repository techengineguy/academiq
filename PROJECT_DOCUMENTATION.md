# Academiq - Educational Management System

> A comprehensive, enterprise-grade Educational Management System (EMS) built with Laravel 13, Livewire 4, and modern web technologies.

---

## 📋 Table of Contents

- [Project Overview](#project-overview)
- [Technology Stack](#technology-stack)
- [Architecture](#architecture)
- [Database Schema](#database-schema)
- [Features & Modules](#features--modules)
- [User Roles](#user-roles)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)

---

## 📚 Project Overview

**Academiq** is a full-featured Educational Management System designed for schools, colleges, and educational institutions. It provides a complete solution for managing:

- Academic administration (classes, subjects, timetables)
- Student management and admissions
- Staff and faculty management
- Exam management and grading
- Attendance tracking
- Financial operations (fees and payroll)
- Hostel management
- Leave management
- Communications and notifications
- Document generation
- Activity auditing

The system is built as a **multi-institutional SaaS platform**, allowing multiple educational institutions to operate independently on a single deployment with complete data isolation.

---

## 🛠 Technology Stack

### Backend
- **PHP 8.3+** - Server-side language
- **Laravel 13** - Web application framework
- **Livewire 4.1** - Reactive server-side UI components
- **Flux 2.13.1** - UI component library
- **TallStackUI** - Additional UI components
- **Laravel Fortify** - Authentication with 2FA support

### Frontend
- **TailwindCSS 4.0** - Utility-first CSS framework
- **Vite** - Build tool and development server
- **Alpine.js** - Lightweight interactivity (via Livewire)

### Database
- **SQLite** - Development (default)
- **MySQL 8.0+ / MariaDB** - Production
- **61 database migrations** with comprehensive schema

### Testing & Quality
- **Pest PHP 4.6** - Modern testing framework
- **Laravel Pint** - PHP code style fixer
- **PHPUnit** - Unit testing
- **Mockery** - Mocking library

### Other Tools
- **Composer** - PHP dependency management
- **npm** - JavaScript dependency management
- **Laravel Artisan** - Command-line interface

---

## 🏗 Architecture

### Design Patterns

#### Multi-Tenancy
- Institution-based isolation
- Each institution has independent data
- All major tables include `institution_id` foreign key
- Supports multiple institutions in a single database

#### Role-Based Access Control (RBAC)
- 6 primary roles: `admin`, `teacher`, `student`, `parent`, `staff`, `accountant`
- Granular permission system
- Dynamic role-permission mapping
- User-role relationships via junction tables

#### Livewire Component Architecture
- Server-side reactive components
- No separate REST API layer
- State management handled server-side
- Real-time form validation and updates

### Key Architectural Decisions

✅ **No Separate API** - Livewire manages all interactions  
✅ **Database Sessions** - Persistent session storage  
✅ **UUID + ID Hybrid** - External API identification with internal relationships  
✅ **Soft Deletes** - Data preservation across entities  
✅ **Institutional Isolation** - Database-level multi-tenancy  
✅ **Fortify Authentication** - Built-in auth with 2FA  
✅ **Modern Tooling** - Vite + TailwindCSS  

---

## 🗄 Database Schema

### 61 Migration Tables

#### Core Management (4 tables)
| Table | Purpose |
|-------|---------|
| `institutions` | Institution profiles |
| `users` | User authentication & profiles |
| `roles` | Role definitions |
| `permissions` | Permission definitions |

#### Academic Module (11 tables)
| Table | Purpose |
|-------|---------|
| `academic_years` | School year management |
| `classes` | Classroom definitions |
| `sections` | Class divisions |
| `subjects` | Course subjects |
| `class_subjects` | Class-subject relationships |
| `timetables` | Class schedules |
| `time_slots` | Scheduling units |
| `lesson_plans` | Teaching plans |
| `academic_calendar` | Holidays, exams, events |

#### Student Management (7 tables)
| Table | Purpose |
|-------|---------|
| `students` | Student profiles (admission_number, blood_group, medical_conditions) |
| `student_parents` | Parent-student relationships |
| `student_promotions` | Class promotion history |
| `student_scholarships` | Scholarship tracking |
| `scholarships` | Scholarship definitions |
| `admissions_inquiries` | Prospective student tracking |
| `admission_applications` | Application processing |

#### Faculty & Staff (3 tables)
| Table | Purpose |
|-------|---------|
| `teachers` | Teacher profiles (qualification, designation) |
| `staff` | Non-teaching staff |
| `payroll` | Monthly salary processing |

#### Attendance (2 tables)
| Table | Purpose |
|-------|---------|
| `attendances` | Student attendance records |
| `staff_attendances` | Teacher/staff attendance |

#### Exams & Assessments (5 tables)
| Table | Purpose |
|-------|---------|
| `exams` | Exam definitions (mid_term, final, unit_test) |
| `exam_schedules` | Exam timing |
| `exam_results` | Student grades |
| `grade_scales` | Grade configurations (A+, B, etc.) |
| `assignments` | Assignment definitions |
| `assignment_submissions` | Student submissions |

#### Finance (5 tables)
| Table | Purpose |
|-------|---------|
| `fee_types` | Fee categories (tuition, sports, etc.) |
| `fee_structures` | Fee configurations per class |
| `fee_invoices` | Billing records |
| `fee_invoice_items` | Invoice line items |
| `fee_payments` | Payment tracking |

#### Hostel Management (4 tables)
| Table | Purpose |
|-------|---------|
| `hostel_buildings` | Dormitory buildings |
| `hostel_rooms` | Individual rooms |
| `hostel_allocations` | Student room assignments |
| `hostel_visitors` | Visitor tracking |

#### Leave Management (2 tables)
| Table | Purpose |
|-------|---------|
| `leave_types` | Leave classifications |
| `leave_applications` | Leave requests & approval |

#### Communications (4 tables)
| Table | Purpose |
|-------|---------|
| `announcements` | Institution-wide notices |
| `events` | Event management |
| `event_participants` | Event attendance |
| `messages` | User-to-user messaging |

#### Documents (7 tables)
| Table | Purpose |
|-------|---------|
| `certificates` | Certificate issuance records |
| `id_cards` | ID card generation |
| `document_templates` | Template management |
| `complaints` | Complaint tracking |
| `activity_logs` | Audit trails |
| `sms_logs` | SMS history |
| `email_logs` | Email history |

#### System (3 tables)
| Table | Purpose |
|-------|---------|
| `settings` | Institution configuration |
| `backups` | Backup records |
| `cache` | Cache storage |

---

## ✨ Features & Modules

### 1. Academic Management
- Academic year configuration
- Class and section management
- Subject assignment to classes
- Timetable creation with time slots
- Lesson plan development
- Academic calendar with holidays and exam dates

### 2. Student Management
- Student admission and enrollment
- Parent-student relationship management
- Student profile management
- Student promotion between classes
- Scholarship tracking and allocation
- Admission inquiry tracking
- Student status management (active, transferred, graduated, dropped)

### 3. Faculty & Staff Management
- Teacher profiles with qualifications
- Staff management (administrative, support roles)
- Employment type tracking (permanent, temporary, contract)
- Emergency contact information
- Role and permission assignment

### 4. Academics - Exams & Grading
- Exam creation (mid-term, final, unit tests, practicals)
- Exam scheduling and timetable
- Result recording and publication
- Grade scale configuration (A+, A, B, C, D, F)
- Automatic grade calculation
- Result reports and analytics

### 5. Attendance Management
- Student attendance tracking
- Teacher/Staff attendance tracking
- Attendance reports
- Absence notifications
- Permission-based attendance access

### 6. Finance & Fee Management
- Fee type configuration
- Fee structure setup (per class, per student)
- Invoice generation and management
- Payment tracking and reconciliation
- Refund management
- Multi-currency support
- Payment gateway integration ready

### 7. Hostel Management
- Building management (boys, girls, mixed)
- Room inventory management
- Student room allocation
- Visitor tracking and management
- Warden assignment and management

### 8. Leave Management
- Leave type configuration (casual, sick, emergency, etc.)
- Leave application workflow
- Approval mechanism with role-based rules
- Leave balance tracking
- Leave report generation

### 9. Communications Hub
- Announcements and notices
- Event management and participant tracking
- Internal messaging system
- Notification system
- SMS integration logging
- Email integration logging

### 10. Document Management
- Certificate generation and issuance
- ID card creation and printing
- Document template management
- Custom document creation

### 11. Staff Payroll
- Monthly payroll processing
- Allowances and deductions tracking
- Tax calculation
- Payment status tracking (pending, paid, on_hold)
- Unique constraint: one payroll per staff per month
- Payroll reports

### 12. System Administration
- Activity audit logs (complete audit trail)
- System backup management
- Institution settings and configuration
- User management and role assignment
- Permission management
- Two-factor authentication (TOTP-based)
- Cache management

---

## 👥 User Roles

| Role | Permissions |
|------|-------------|
| **Admin** | Full system access, institution management, user management, settings |
| **Teacher** | Student management, attendance, assignments, grade entry, lesson planning |
| **Student** | View assignments, attendance, grades, messages, events |
| **Parent** | View child's progress, attendance, fees, messages |
| **Staff** | Administrative support, record keeping, data entry |
| **Accountant** | Fee management, payment processing, payroll, financial reports |

---

## 📁 Project Structure

```
academiq/
├── app/
│   ├── Models/              # Eloquent models (61 models)
│   ├── Http/
│   │   ├── Controllers/     # Controller logic
│   │   └── Middleware/      # Route middleware
│   ├── Livewire/
│   │   ├── Pages/           # Livewire page components
│   │   └── Actions/         # Reusable Livewire actions
│   ├── Concerns/            # Shared traits
│   ├── Providers/           # Service providers
│   └── Actions/             # Action classes
├── database/
│   ├── migrations/          # 61 migration files
│   ├── factories/           # Model factories for testing
│   └── seeders/             # Database seeders
├── resources/
│   ├── views/               # Blade templates
│   │   ├── layouts/         # Layout components
│   │   └── components/      # Reusable components
│   ├── css/                 # TailwindCSS styles
│   └── js/                  # JavaScript
├── routes/
│   ├── web.php              # Web routes
│   ├── auth.php             # Auth routes
│   ├── app.php              # Application routes
│   └── settings.php         # Settings routes
├── config/                  # Configuration files
├── storage/                 # File storage
├── tests/                   # Test files (Pest)
├── public/                  # Public assets
├── bootstrap/               # Bootstrap files
├── vendor/                  # Composer dependencies
├── node_modules/            # npm dependencies
├── .env.example             # Environment example
├── composer.json            # PHP dependencies
├── package.json             # JavaScript dependencies
├── vite.config.js           # Vite configuration
├── tailwind.config.js       # TailwindCSS configuration
└── phpunit.xml              # PHPUnit configuration
```

---

## 🚀 Getting Started

### Installation

```bash
# Clone the repository
git clone <repository-url> academiq
cd academiq

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

### Development

```bash
# Run development server
php artisan serve

# Watch for CSS/JS changes
npm run dev

# Run tests
./vendor/bin/pest

# Format code with Pint
./vendor/bin/pint

# Clear cache
php artisan cache:clear
```

### Database

```bash
# Run migrations
php artisan migrate

# Seed database (if seeders exist)
php artisan db:seed

# Roll back migrations
php artisan migrate:rollback

# Refresh database
php artisan migrate:refresh
```

---

## 📋 Configuration

### Environment Variables (.env)

```env
APP_NAME=Academiq
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_CONNECTION=mysql
# DB_HOST=localhost
# DB_PORT=3306
# DB_DATABASE=academiq
# DB_USERNAME=root
# DB_PASSWORD=

MAIL_MAILER=log
# MAIL_HOST=smtp.mailtrap.io
# MAIL_PORT=587
# MAIL_USERNAME=
# MAIL_PASSWORD=

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_DRIVER=database
```

---

## 🔐 Security Features

✅ **Two-Factor Authentication** - TOTP-based 2FA  
✅ **Role-Based Access Control** - Granular permissions  
✅ **Audit Trails** - Complete activity logging  
✅ **Soft Deletes** - Data recovery capability  
✅ **Multi-Tenancy** - Institution data isolation  
✅ **CSRF Protection** - Laravel's built-in CSRF tokens  
✅ **SQL Injection Prevention** - Eloquent ORM parameterized queries  
✅ **XSS Protection** - Blade template escaping  

---

## 📊 Database Statistics

- **61 Migration Files** creating comprehensive schema
- **Multiple Relationships** - Properly indexed for performance
- **Soft Deletes** - Data preservation across major entities
- **UUID Support** - External API identification
- **Audit Trails** - Complete activity logging

---

## 🧪 Testing

The project uses **Pest PHP** for modern, expressive testing:

```bash
# Run all tests
./vendor/bin/pest

# Run specific test file
./vendor/bin/pest tests/Feature/StudentTest.php

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test method
./vendor/bin/pest --filter=testCreateStudent
```

---

## 📦 Key Dependencies

### Backend Packages
- `laravel/framework: ^13.0`
- `livewire/livewire: ^4.1`
- `livewire/flux: ^2.13`
- `laravel/fortify: ^1.20`
- `tallstackui/tallstackui: ^4.0`

### Frontend Packages
- `tailwindcss: ^4.0`
- `@tailwindcss/forms: ^0.5`

### Development Tools
- `phpunit/phpunit: ^11.0`
- `pestphp/pest: ^4.6`
- `laravel/pint: ^1.17`

---

## 🤝 Contributing

When contributing to Academiq:

1. Follow Laravel and PHP coding standards (enforced by Pint)
2. Write tests for new features
3. Maintain database migration best practices
4. Keep component structure organized
5. Document complex business logic

---

## 📝 License

This project is proprietary software. All rights reserved.

---

## 🆘 Support

For issues and support:
- Check existing documentation
- Review database migrations for schema details
- Consult Livewire and Flux documentation
- Review model relationships in `app/Models/`

---

## 🎯 Roadmap

**Current Features:**
- ✅ Complete academic management
- ✅ Student management and admissions
- ✅ Exam and grading system
- ✅ Finance and fee management
- ✅ Hostel management
- ✅ Staff payroll
- ✅ Leave management
- ✅ Communications hub

**Potential Future Enhancements:**
- Mobile app (React Native/Flutter)
- Advanced analytics dashboard
- AI-powered grade predictions
- Integration with payment gateways
- SMS/WhatsApp integration
- Online class integration
- Blockchain certificates

---

**Last Updated:** April 28, 2026  
**Version:** 1.0.0  
**Status:** Production Ready
