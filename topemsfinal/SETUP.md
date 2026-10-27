# Run the EMS PHP/MySQL project on Windows with XAMPP

## 1. Put the project in XAMPP

Extract the ZIP. Copy its `Project_newone_uiux` folder into `C:\xampp\htdocs` and rename that copied folder to `ems_portal`.

Your files should include:

- `C:\xampp\htdocs\ems_portal\login.php`
- `C:\xampp\htdocs\ems_portal\database\schema.sql`
- `C:\xampp\htdocs\ems_portal\includes\config.php`

Do not run this version with VS Code Live Server, Python's HTTP server, or by double-clicking PHP files. It needs PHP and MySQL. Use PHP 8.1 or newer with PDO MySQL; XAMPP's MariaDB is supported.

## 2. Start Apache and MySQL

Open XAMPP Control Panel. Click **Start** next to **Apache** and **MySQL**. Both should show as running.

## 3. Import the database

1. Open `http://localhost/phpmyadmin`.
2. Select the **Import** tab at the server level.
3. Choose `C:\xampp\htdocs\ems_portal\database\schema.sql`.
4. Click **Import / Go**.
5. Confirm that the database `ems_portal` contains `users`, `feedback`, and `salaries`.

The SQL creates a separate database and does not delete or modify your old `ems` or `emsdb` database. Fresh setup creates only admin@gmail.com; all business tables begin empty. Re-importing the schema does not reset existing account passwords or clear saved records.

## 4. Check the connection

Open `includes/config.php`. The defaults are:

| Setting | Value |
|---|---|
| Host | `127.0.0.1` |
| Port | `3306` |
| Database | `ems_portal` |
| Username | `root` |
| Password | empty string |

If you changed your MySQL port or set a root password, update the corresponding value. Environment variables `EMS_DB_HOST`, `EMS_DB_PORT`, `EMS_DB_NAME`, `EMS_DB_USER`, and `EMS_DB_PASSWORD` can override these settings.

## 5. Open the first login page

Open **`http://localhost/ems_portal/login.php`**.

| Portal | Username | Password |
|---|---|---|
| Admin | `admin@gmail.com` | `Admin@123` |


Enter your username and password; you are taken to the Admin or Employee portal automatically based on your account. There are no initial employees. Newly created employees use the individual username/password assigned by the admin.

Logouts remain at the bottom of the sidebar and return to the login page. Role authorization is enforced by PHP. An employee cannot open admin pages, and an admin cannot use employee pages without logging out and signing in as an employee.

## 6. Demonstrate the three completed steps

### Database connection and login

Log in as Admin. The dashboard reads counts from MySQL. Refresh after a change to see updated counts.

### Admin employee CRUD

1. Open **Employees** and create a new employee with a unique employee ID, username, and email.
2. Confirm the employee appears in the table.
3. Click **Edit**, update the employee's department, and save.
4. Leave the password field empty to keep their current password. Enter a new password to reset it; existing sessions for that employee are invalidated.
5. Log out, sign into the Employee portal with the new account, and verify the profile.
6. Delete the test employee at the end of the demonstration. Deleting an employee also deletes their feedback and salary records; the confirmation states this.

### Employee feedback CRUD

1. Log into the Employee portal and open **Feedback**.
2. Create feedback and see it in the history.
3. Edit it and save. Edits return the record to Pending and clear the old admin response for a new review.
4. Log out and enter the Admin portal. Open **Feedback**, write a response and set Reviewed or Resolved.
5. Log back in as the employee and see the response.
6. Delete the feedback record.

Employees can only read, edit and delete their own records. Their employee ID comes from their PHP session, not a form field.

### Admin salary CRUD and employee payslip

1. In Admin → **Salary Management**, select an employee and month.
2. Enter basic salary, allowances and deductions. The PHP server calculates net salary as basic + allowances − deductions.
3. Save and read the table. A second record for the same employee/month is rejected.
4. Edit the deductions and save; verify the new total.
5. Log into that employee's portal and open **My Payslips**. Select **View Payslip**, then **Print / Save as PDF** if desired.
6. Return to the admin account and delete the test salary record.

“Paid” is a recorded payment status, not a bank transfer. Salary payments themselves are not integrated.

## 7. Optional: import the old EMS database

This is optional. The backend already works without importing old records.

Keep your old database available as `ems` (or replace the source option with its actual name). Open PowerShell:

```powershell
cd C:\xampp\htdocs\ems_portal
& C:\xampp\php\php.exe tools\import-legacy.php --source=ems --reset-password="Imported@123" --salary-month="2025-12"
```

Run this once after the new schema import. It copies old employees, feedback and salaries into the new tables in one transaction. The old database is read-only during import.

Imported employees use their old username and the new reset password you supply. The old database contains plaintext or truncated password hashes, so these passwords are deliberately replaced. Use Employees → Edit to assign an individual password afterwards.

Old salary records have no month or paid status: the supplied month is used, and their status is Pending. Name matches must identify exactly one employee. Invalid emails/dates, duplicate employees, unmatched feedback or salaries, and ambiguous matches are skipped and counted. A phone number already truncated in the old database cannot be recovered by the script. Correct those old records manually before importing. Re-running does not duplicate existing employee accounts; the importer only links feedback/salary to employees newly imported in that run.

## 8. Change the initial admin password

From the project folder:

```powershell
& C:\xampp\php\php.exe tools\change-password.php --username=admin@gmail.com --password="YourNewPassword123"
```

This also invalidates the admin's existing sessions. Use the new password on the next login.

## Troubleshooting

- **PHP source code appears / pages download:** run through XAMPP Apache, not Live Server.
- **Unable to complete the request:** check that MySQL is running, import `database/schema.sql`, and check `includes/config.php`. Inspect Apache/PHP's error log for details.
- **Database unknown:** the new database name is `ems_portal`, not the old `ems` or `emsdb`.
- **Invalid form token:** reload the page and submit the form again.
- **Old HTML bookmarks no longer open:** portal pages now end in `.php`; start at `login.php`. Root login HTML files redirect to the PHP equivalents.
- **Port conflict:** use Apache's configured port in the URL, for example `http://localhost:8080/ems_portal/login.php`.

## Scope of this version

Database-backed modules: authentication, employee profile, both dashboards, employee feedback CRUD, admin feedback responses, admin employee CRUD, admin salary CRUD, employee salary/payslips.

Attendance, leave, departments and monthly reports are now database-backed. Existing installations must import database/upgrade.sql before using the updated files. See DATABASE_FIXES.md for the migration and complete manual test checklist. Employee CRUD includes feedback and pending leave requests. Git repository work for the college review remains a separate task.

This project was functionally tested using PHP 8.3 and MariaDB 10.11. See `TESTING.md`. Browser visual testing and your local XAMPP configuration still need checking on your machine.
