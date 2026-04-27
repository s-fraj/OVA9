<?php
// ── OVA9 Configuration ─────────────────────────────────────────────
// XAMPP defaults — edit only if you changed MySQL settings
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_USER',    'root');
define('DB_PASS',    '');           // XAMPP default = empty password
define('DB_NAME',    'ova9');
define('JWT_SECRET', 'ova9-secret-key-change-in-production-2026');
define('JWT_EXPIRE', 60 * 60 * 24 * 7);   // 7 days
define('APP_NAME',   'OVA9');
define('FREE_DAILY_LIMIT', 3);             // free scans per day
