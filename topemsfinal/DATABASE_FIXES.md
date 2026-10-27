# Database corrections

Departments, attendance, employee leave requests, admin leave decisions and monthly reports now read and write MySQL. The previous pages were static templates. The existing employee, salary, feedback and payslip database operations are retained. Shared navigation and POST logout now apply to every module.

## Existing installation (preserve your records)
1. Back up ems_portal in phpMyAdmin (Export).
2. Replace the project files in your XAMPP htdocs folder with this version.
3. Start Apache and MySQL.
4. In phpMyAdmin, import database/upgrade.sql. It adds the missing tables without dropping existing records. It can be imported again safely.
5. Verify includes/config.php: database ems_portal, host 127.0.0.1, port 3306, username root, password blank for default XAMPP. Change these to match your installation.
6. Open http://localhost/Project_newone_uiux/login.php and sign in with your existing credentials.

For a NEW installation import database/schema.sql instead. Do not import both files for a new installation. Existing demo passwords are documented in SETUP.md; change them before deployment.

## Checks on your computer
- Admin: add, rename and delete an unused department. Renaming updates assigned employees; deleting an assigned department is blocked.
- Admin: add attendance, edit it, refresh and verify persistence. Duplicate employee/date records are blocked. Delete a test record.
- Employee: verify only your own attendance is visible.
- Employee: create, edit and delete pending leave. Invalid dates and overlapping pending/approved requests are rejected.
- Admin: approve/reject leave and add a response. Employee: verify decision after refresh; decided requests cannot be edited/deleted.
- Admin: view a monthly report and download its CSV; compare it with attendance, salary and leave records.
- Verify existing employee CRUD, salary CRUD, feedback/replies and employee payslips.
- Sign in as a second employee and verify that attendance, leave, feedback and payslips are isolated.
- Verify logout returns to the main login and employee accounts cannot open admin URLs.

Attendance is maintained by admins. Department/designation assignment is maintained by admins. Employee profile remains read-only. Monthly reports are generated from live records; they do not require a separate report table. Empty new modules show no records until you enter data; no sample attendance or leave is inserted.

## Validation limitation
PHP syntax parsing passed for all 27 PHP files, along with static role and ownership checks. The editing environment does not have PHP or MySQL available. Live PHP execution and MySQL integration must be checked in XAMPP using the checklist above. Do not treat this package as runtime-tested.
