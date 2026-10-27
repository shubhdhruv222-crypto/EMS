<?php
// XAMPP defaults. Use a dedicated MySQL account when deploying.
return [
    'host' => getenv('EMS_DB_HOST') ?: '127.0.0.1',
    'port' => getenv('EMS_DB_PORT') ?: '3306',
    'database' => getenv('EMS_DB_NAME') ?: 'ems_portal',
    'username' => getenv('EMS_DB_USER') ?: 'root',
    'password' => getenv('EMS_DB_PASSWORD') !== false ? getenv('EMS_DB_PASSWORD') : '',
];
