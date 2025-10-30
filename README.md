# Waste Management Portal (Wasteman)

Simple PHP (mysqli) application for managing private and commercial waste customers, payments, and admin controls. Tailwind CSS UI with role-based navigation.

## Features
- Customer management (private/commercial)
- CSV import helpers
- Payment logging and review
- Admin controls (rates, guide)
- Setup Wizard (`setup.php`) for database configuration
- Backup & Restore (SQL export/import)

## Quick Start

### XAMPP (Windows/Mac)
1. Extract the project to your web root (e.g. `c:\xampp\htdocs\waste`).
2. Visit `http://localhost/waste/setup.php`.
3. Enter your MySQL details (server, username, password, database) and save.
4. Login at `http://localhost/waste/login.php`.

### cPanel (Shared Hosting)
1. Create a MySQL database and user in cPanel, grant privileges.
2. Upload the project ZIP and extract to your desired folder (root or a subfolder).
3. Navigate to `https://yourdomain.com/<subfolder>/setup.php`.
4. Enter the DB details and save; `config.php` will be created.
5. Go to `login.php` and sign in.

Notes:
- Works from any subfolder; all links are relative.
- `config.php` is ignored by Git to keep credentials private.

## Backup & Restore
- Admins can visit `backup_restore.php` to:
  - Backup: Export all tables and data to `backups/backup_YYYYMMDD_HHMMSS.sql`.
  - Restore: Upload an SQL file to recreate tables and data.

## Development
- PHP 8+ recommended; MySQL/MariaDB.
- Update or extend schema via SQL; then use Backup & Restore to migrate.

## GitHub
- Repository: `https://github.com/dapoalla/wasteman`
- To push changes:
  - `git add .`
  - `git commit -m "Add setup wizard, backup/restore, docs"`
  - `git push`