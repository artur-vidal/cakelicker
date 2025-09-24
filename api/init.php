<?php

    // definição de constantes
    define("SESSION_COOKIE_NAME", 'cakelicker_session');

    define("IS_LOCAL", (isset($_SERVER['REMOTE_ADDR'])) ? ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') : true);
    define("UPLOAD_DIR", './uploads/');

    // header para padronizar tipo das respostas e garantir interpretação externa
    header('Content-Type: application/json; charset=utf-8');

    // --- IMPORTANDO ARQUIVOS ESSENCIAIS ---

    // ambiente
    require_once './vendor/autoload.php';
    use Dotenv\Dotenv;
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    // outros arquivos
    require_once 'utils/functions.php';
    require_once 'config/db.php';

    // definindo configurações do projeto
    ini_set('display_errors', IS_LOCAL);

?>