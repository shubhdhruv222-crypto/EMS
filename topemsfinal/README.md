# Employee Management System (EMS)

PHP 8.1+ / MySQL application with admin and employee portals, employee management, attendance, leave, salary and payslips, feedback, departments, and reports.

The complete application source from `topemsfinal.zip` is included. The existing application UI and PHP behavior are preserved. Publishing source on GitHub does not deploy the PHP application.

## Run locally

1. Start Apache and MySQL in XAMPP.
2. Import `database/schema.sql` into a fresh local database.
3. Open `http://localhost/topemsfinal/login.php` when this folder is installed as `C:\xampp\htdocs\topemsfinal`.

The local starter administrator is `admin@gmail.com` / `Admin@123`. This is a documented setup account, not a production credential. Change its password before making any installation publicly accessible.

See [SETUP.md](SETUP.md) for detailed setup and [DEPLOYMENT.md](DEPLOYMENT.md) for production deployment. Existing installations use `database/upgrade.sql`; back up their database first.

## Source and data

The schema creates an initial administrator and empty business tables. No employee records, payroll exports, database dumps, or hosting credentials are included. `database/sample_data.sql` is intentionally a no-op. Database connection settings are read from `EMS_DB_HOST`, `EMS_DB_PORT`, `EMS_DB_NAME`, `EMS_DB_USER`, and `EMS_DB_PASSWORD` environment variables.

PHP syntax checks passed for all 28 PHP files during source publication. The historical functional test checklist in [TESTING.md](TESTING.md) is supplied with the project; it is not a new production deployment test.
