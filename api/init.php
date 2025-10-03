<?php

    define('IS_LOCAL', (isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') : true);
    define('SESSION_COOKIE_NAME', 'cakelicker_session');
    define('UPLOAD_DIR', __DIR__ . '\\uploads\\');
    define('USER_ENDPOINT_PRESETS', ['first', 'last']);
    define('SAVES_PER_USER', 3);

    require_once __DIR__ . '\\vendor\\autoload.php';
    use Dotenv\Dotenv;
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    require_once __DIR__ . '\\config\\db.php';

    if(!is_dir(__DIR__ . '\\uploads'))
        mkdir(__DIR__ . '\\uploads');

    if(!is_dir(__DIR__ . '\\uploads\\saves'))
        mkdir(__DIR__ . '\\uploads\\saves');
    
    ini_set('display_errors', IS_LOCAL);

?>