# PHP/MySQL deployment

## Requirements

Use a PHP 8.1+ host with PDO MySQL, MySQL/MariaDB, Apache 2.4, HTTPS, and permission to honor the bundled `.htaccess` files (`AllowOverride` with authorization, directory index and options directives). GitHub Pages cannot execute this application. Do not deploy the PHP source as static files.

## Deployment steps

1. Keep the installation behind the hosting provider's access restriction until setup and password rotation are complete.
2. Deploy this repository, including hidden `.htaccess` files, to the configured web directory. Exclude `.git`, local credentials, exports, backups, and personal database dumps.
3. Create a production database and a dedicated database user. Supply its values through the host's environment settings: `EMS_DB_HOST`, `EMS_DB_PORT`, `EMS_DB_NAME`, `EMS_DB_USER`, `EMS_DB_PASSWORD`. All five must be set explicitly; the bundled fallback is only for local XAMPP. The app does not automatically load `.env` files.
4. Import `database/schema.sql` into the fresh database. If the host assigns its own database name or forbids `CREATE DATABASE`, make a private deployment copy that omits `CREATE DATABASE` and every `USE ems_portal` line, then import it into the host-created database. Do not commit a database export. For an existing installation, back up first and use the additive upgrade instead.
5. From a private terminal on the host, run `php tools/change-password.php --username=admin@gmail.com --password="YOUR_UNIQUE_PASSWORD"`, substituting a strong unique password of 8–72 bytes. Do not reuse the starter password. Avoid shared terminals, retained shell history, and build logs when supplying it. Do not run the reset tool against production; it restores the starter account.
6. Enable HTTPS and confirm that the login page and redirects use HTTPS. If the host terminates HTTPS at a proxy, configure its trusted HTTPS forwarding so PHP sees HTTPS and sets secure session cookies.
7. Verify that direct requests to `includes/config.php`, `database/schema.sql`, `tools/reset-database.php`, `.git/config`, and `.env` return 403 or 404. For a non-Apache host, configure equivalent directory and hidden-file restrictions before allowing visitors.
8. Test admin login, employee creation, employee login, attendance, leave, feedback, salary and payslips, reports, role restrictions, and logout. Remove the temporary test records through the app. Keep production errors out of responses and enable private server error logging.
9. Remove the temporary hosting access restriction only after these checks pass. Set up private database backups through the hosting provider.

## Required hosting access

Live deployment requires an existing PHP/MySQL hosting account and access to its deployment settings, database provisioning, and private terminal (or an equivalent provider-supported way to rotate the initial password). No hosting account, deployment endpoint, or production database credentials were found in the project or available deployment environment during source publication. Connect the intended hosting account to continue; do not put passwords or access tokens in this repository.
