# Clean EMS database

Fresh installations: import `database/schema.sql`. It creates only Administrator, with email and login username `admin@gmail.com` and password `Admin@123`. All five business tables start empty. The existing username login form and all application pages are unchanged. `database/sample_data.sql` is now a no-op.

Importing the schema into an existing database preserves its records and passwords. It does not reset that database.

## Existing database reset — requires explicit owner confirmation

No live reset has been performed. First inspect `includes/config.php` and any `EMS_DB_*` environment overrides. Run the preview from the project folder:

```powershell
& C:\xampp\php\php.exe tools\reset-database.php
```

The preview displays the target and counts without writing records. Before approving a reset, stop Apache and other application writers, export the target database with phpMyAdmin (including structure and data), and verify the backup can be restored. Keep that backup outside the web directory. Ensure all six application tables exist; older installations may need `database/upgrade.sql` first.

After the owner explicitly approves erasing that exact target, run the following, substituting the preview's database name if environment overrides change it:

```powershell
& C:\xampp\php\php.exe tools\reset-database.php --confirm=ERASE:ems_portal
```

The tool refuses unexpected/missing tables or non-InnoDB tables. It deletes feedback, salary, attendance, leave, users and departments within a transaction, inserts the administrator with a PHP password hash, verifies the final state, and rolls back on failure. It preserves table definitions and auto-increment sequences and invalidates previous user sessions. Keep other writers stopped until verification completes, then restart Apache.

Sign in at `http://localhost/topemsfinal/login.php` using `admin@gmail.com` / `Admin@123`. Dashboard counts should be zero; create employees and departments through the existing admin screens. No demo records are needed. Do not run the legacy importer unless you deliberately want old data restored.
