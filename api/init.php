<?php

    define('IS_LOCAL', (isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') : true);
    define('SESSION_COOKIE_NAME', 'cakelicker_session');
    define('UPLOAD_DIR', __DIR__ . '\\uploads\\');
    define('USER_ENDPOINT_PRESETS', ['first', 'last']);
    define('SAVES_PER_USER', 3);

    header('Content-Type: application/json; charset=utf-8');

    require_once __DIR__ . '\\vendor\\autoload.php';
    use Dotenv\Dotenv;
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    require_once 'config/db.php';
    
    ini_set('display_errors', IS_LOCAL);

?>