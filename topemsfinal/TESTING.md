# Updated validation checklist

See DATABASE_FIXES.md for the database migration and module-by-module checks.

# Functional validation

Tested on PHP 8.3.6 with PDO MySQL and MariaDB 10.11.14, using a fresh database and HTTP requests with independent cookie sessions. The project was tested under a subfolder URL, matching the typical XAMPP deployment layout.

| Check | Result |
|---|---|
| New schema imports and initial password hashes authenticate | Passed |
| Admin and employee accounts enter their own dashboard | Passed |
| Wrong-portal credentials and SQL injection login attempt rejected | Passed |
| Direct page access requires login; wrong role returns HTTP 403 | Passed |
| Admin creates an employee who can log in immediately | Passed |
| Duplicate employee identifiers / usernames rejected | Passed |
| Admin reads, searches and edits employee data; profile shows changes | Passed |
| Password reset invalidates the employee's old session | Passed |
| Employee creates, reads, updates and deletes own feedback | Passed |
| A second employee cannot edit or delete the first employee's feedback | Passed |
| Admin response and status appear in the employee portal | Passed |
| HTML/script text in user content is escaped | Passed |
| Missing form token rejected with HTTP 403 | Passed |
| Admin salary create, read, update and delete persist in MySQL | Passed |
| Salary net amount calculated on server; supplied fake total ignored | Passed |
| Duplicate employee/month salary rejected | Passed |
| Negative amounts and deductions exceeding earnings rejected | Passed |
| Employee payslip reads the updated salary | Passed |
| Another employee cannot open the payslip by changing its ID | Passed |
| Deleting employee prevents further access from their existing session | Passed |
| All 14 portal pages require the correct PHP role | Passed |
| Both logout forms invalidate session and return to first login page | Passed |
| GET request cannot perform logout | Passed |
| Development router blocks database SQL and CLI import over HTTP | Passed |
| Optional old EMS import tested against the uploaded ems.sql | Passed |
| PHP syntax and JavaScript syntax | Passed |

The legacy import copied one valid employee, two feedback records and one salary record from the supplied old schema. The other employee's invalid email was skipped. Imported passwords were reset and salary month supplied explicitly.

No test records are packaged: setup starts with only the two initial login accounts. Salary and feedback tables begin empty.

## Local verification still needed

Open the project in your own XAMPP installation using SETUP.md. Confirm your Apache/MySQL ports and database credentials, and review desktop/mobile layout and payslip print formatting in your browser. Automated browser visual testing was unavailable in the build environment; these checks used real HTTP requests, PHP and MySQL.

## Short review demonstration

1. Log in as Admin; create and edit an employee.
2. Log out; sign into Employee with that employee's credentials.
3. Create, read and edit feedback.
4. Log out; respond to feedback as Admin.
5. Create salary, read it, edit the deductions and save.
6. View response and payslip as Employee; delete feedback.
7. Delete salary and the test employee as Admin.
8. Log out; show the login page.
