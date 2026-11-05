# EcoTrace - E-Waste Pickup and Tracking

PHP, MySQL, HTML and CSS website for submitting and tracking e-waste pickup requests. The site has no JavaScript and does not process online payments. It displays a fixed charge for each device category plus one flat pickup charge per request.

## Main files

- `config.php` - session, database connection and fee settings
- `index.php` - home page and contact form
- `register.php`, `login.php`, `logout.php` - account pages
- `request.php` - pickup request form and fee display
- `dashboard.php` - a user's requests and cancellation option
- `track.php` - public tracking code and status timeline
- `centers.php`, `awareness.php` - information pages
- `admin_dashboard.php`, `update_status.php` - request administration
- `includes/` - shared page header and footer
- `assets/css/style.css` - website styles and CSS-only mobile menu
- `sql/ecotrace.sql` - database and sample centers for a fresh install

## Setup with XAMPP

1. Copy the `ecotrace` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. Import `sql/ecotrace.sql` through phpMyAdmin.
4. Check database settings in `config.php`.
5. Open `http://localhost/ecotrace/` and register an account.
6. To use the admin page for a demo, set your account role to `admin` in the `users` table.

## Fee display

The category charges are fixed per item, with one additional flat pickup charge per request. The example amounts are listed on the home and request pages and can be changed in `config.php`. They are displayed only; the site does not request card details, record payment or require payment before an administrator updates a request.

## Core flow

A user registers, submits a pickup request, receives a tracking code and checks its status from the dashboard or tracking page. An administrator reviews the request and records status changes.

## Notes

The project is intended for local academic demonstration. If an older database was already imported, old payment-related columns or tables may remain in that database; this version does not read or use them. Back up the database before making manual schema changes.
