<?php

    require_once __DIR__ . '\\init.php';

    $start_time = microtime(true);

    use Cakelicker\Helpers\{ResponseHelper, ArrayHelper};
    
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $_SERVER['REQUEST_METHOD'];

    $response_builder = null;

    $uri_parts = parse_url($_SERVER['REQUEST_URI']);
    $endpoint_parts = array_slice(explode('/', $uri_parts['path']), 3) ?? null;
    // partes apenas a partir de localhost/cakelicker/api/

    if(!$endpoint_parts || $endpoint_parts[0] == '') {
        $response_builder = ResponseHelper::generateBuilder(true, 200, 'Sem rota', null);
        ResponseHelper::buildAndRespond($response_builder);
    }

    switch($endpoint_parts[0]) {
        case 'users':
            require_once __DIR__ . '\\routes\\users.php';
            break;
        case 'login':
            require_once __DIR__ . '\\routes\\login.php';
            break;
        default:
            $response_builder = ResponseHelper::generateBuilder(true, 200, 'Rota inválida.', null);
            break;
    }

    $response_builder->addAdditionalField('elapsed_seconds_in_server', round(microtime(true) - $start_time, 3));
    ResponseHelper::buildAndRespond($response_builder);
    
?> 