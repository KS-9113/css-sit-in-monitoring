# CCS Sit-In Monitoring System

PHP + MySQL (XAMPP) sit-in monitoring system for University of Cebu — College of Computer Studies.

## Features

- **Public:** Landing page, student registration & login (hashed passwords)
- **Student:** Dashboard, profile, reservations, sit-in history, feedback (1–5 stars)
- **Admin:** Student management, reservations (accept/reject), walk-in sit-in, live PC grid, reports & analytics, feedback CSV export

## Requirements

- XAMPP (Apache + MySQL + PHP 8+)
- phpMyAdmin

## Installation

1. Copy this folder to `C:\xampp\htdocs\ccs-sit-in-monitoring`
2. Start **Apache** and **MySQL** in XAMPP Control Panel
3. Open phpMyAdmin → Import `database/schema.sql`
4. Visit `http://localhost/ccs-sit-in-monitoring/install.php` to set admin password hash
5. **Delete `install.php`** after setup
6. Open `http://localhost/ccs-sit-in-monitoring/`

## Default Admin Login

- **Username:** `admin`
- **Password:** `admin123` (after running `install.php`)

## Configuration

Edit `config/database.php` if your MySQL user/password differs:

```php
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', '/ccs-sit-in-monitoring');
```

## Business Rules

- New students receive **30** sit-in sessions
- Session deducted only when status becomes **Completed** (after checkout)
- One active reservation per student (`Reserved`, `Approved`, or `On Going`)
- PC grid: green = available, orange = in use (`On Going`)
- Walk-in sit-ins from admin are auto-approved and start immediately

## Project Structure

```
ccs-sit-in-monitoring/
├── admin/           # Admin pages
├── api/             # Form handlers & AJAX
├── assets/          # CSS, JS, uploads
├── config/          # Database config
├── database/        # schema.sql
├── includes/        # Shared PHP includes
├── student/         # Student pages
└── index.php        # Landing page
```

## Notes

- Profile uploads go to `assets/uploads/profiles/` (ensure folder is writable)
- Loading animation shows on every page until content loads
- Purple theme via Bootstrap 5 + custom CSS
