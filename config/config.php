<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true, 'secure' => $secure, 'samesite' => 'Lax'
    ]);
    session_start();
}
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'library_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_NAME', 'LibraSpace');
define('BASE_URL', ''); // Example: '/library_management_system' if installed in a subfolder.
