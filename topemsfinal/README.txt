EMS — New UI with PHP/MySQL Backend

Start here: SETUP.md
Copy the project into C:\xampp\htdocs\ems_portal.
Start Apache + MySQL in XAMPP.
Import database/schema.sql in phpMyAdmin.
Open http://localhost/ems_portal/login.php.

Admin: admin@gmail.com / Admin@123
No demo records. See CLEAN_DATABASE.md before resetting an existing database.
Create additional employee accounts through Admin > Employees.

Completed: PHP authentication and role guards; employee own-feedback CRUD;
admin employee CRUD; admin salary CRUD; admin feedback responses;
employee profile, dashboards and payslips connected to MySQL.
Logout is at the sidebar bottom and returns to the first login page.

Optional old-data import: tools/import-legacy.php (CLI only), explained in SETUP.md.
The old database is preserved. Browser localStorage data is not migrated.
Attendance, leave, departments and reports are database-backed.
This version needs PHP + MySQL and cannot run with Live Server/Python HTTP server.
