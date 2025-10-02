<?php

    require_once __DIR__ . '\\init.php';

    use Cakelicker\Helpers\{ResponseHelper, ArrayHelper};
    
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $_SERVER['REQUEST_METHOD'];

    $response = null;

    $uri_parts = parse_url($_SERVER['REQUEST_URI']);
    $endpoint_parts = array_slice(explode('/', $uri_parts['path']), 3) ?? null;
    // partes apenas a partir de localhost/cakelicker/api/

    if(!$endpoint_parts || $endpoint_parts[0] == '')
        ResponseHelper::respond(ResponseHelper::generate(true, 200, 'Sem rota', null));

    switch($endpoint_parts[0]) {
        case 'users':
            require_once __DIR__ . '\\routes\\users.php';
            break;
        case 'login':
            require_once __DIR__ . '\\routes\\login.php';
            break;
        default:
            $response = ResponseHelper::generate(true, 200, 'Rota inválida.', null);
            break;
    }

    ResponseHelper::respond($response);
?>