# Healsync — Hospital Management System (PHP + MySQL)

## Overview
Healsync is a demo Hospital Management System built with PHP (PDO), MySQL, Tailwind CSS, and minimal JavaScript. It demonstrates patient booking, doctor approvals, prescriptions, treatments, billing, and admin management.

## Prerequisites
- XAMPP (Apache + PHP + MySQL) — tested on PHP 8.x
- Composer (optional, if you add libraries)
- Browser

## Installation / Local Setup (XAMPP)
1. Copy the `healsync` folder into `htdocs` (e.g., `C:\xampp\htdocs\healsync`).
2. Start Apache and MySQL via XAMPP Control Panel.
3. Visit `http://localhost/healsync/setup.php` in your browser. This will:
   - Create the `healsync` DB and tables.
   - Seed an admin (admin@hms.com / admin123), doctors (doctor123), and patients (patient123).
4. After setup succeeds, **delete `setup.php`** for security.
5. Login:
   - Admin: `admin@hms.com` / `admin123`
   - Doctor: e.g., `alice@hms.com` / `doctor123`
   - Patient: `john@hms.com` / `patient123`

## File structure
(see full file tree in the project root README or in the ZIP layout above)

## Notes on Security & Best Practices
- Passwords are hashed via `password_hash()`; always keep PHP up to date.
- All DB queries use prepared statements via PDO to prevent SQL injection.
- CSRF tokens implemented via `$_SESSION['csrf_token']` — validate tokens on POST.
- Session cookie flags set for `httponly` and `samesite`.
- For production: use HTTPS, secure cookie flags (`secure`), input validation, and rate-limiting.
- Remove `setup.php` after running.

## Extending the project
- Add email sending for registration and forgot password (PHPMailer recommended).
- Use a JS calendar library (FullCalendar) for doctor calendar views.
- Integrate a real payment gateway (Stripe/PayPal) for live payments.
- Add unit and integration tests (PHPUnit).

## Troubleshooting
- DB connection errors: update DB constants in `includes/config.php`.
- If Tailwind CDN is blocked, you can download Tailwind or use a local build.

Enjoy! — Healsync
