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
        $response_builder = ResponseHelper::builder(true, 200, 'Sem rota', null);
        ResponseHelper::buildAndRespond($response_builder);
    }

    $resource = $endpoint_parts[0] ?? null;
    $resource_id = $endpoint_parts[1] ?? null;
    $subresource = $endpoint_parts[2] ?? null;
    $subresource_id = $endpoint_parts[3] ?? null;

    switch($resource) {
        case 'users':
            require_once __DIR__ . '\\routes\\users.php';
            break;
        case 'login':
            require_once __DIR__ . '\\routes\\login.php';
            break;
        case 'cleanup': // temporario so pra testar, depois vai pra /admin/cleanup
            if($method == 'DELETE') {
                $sc = new Cakelicker\Controllers\SaveController($conn);
                $response_builder = $sc->cleanupSaves();
            } else {
                $response_builder = ResponseHelper::builder(false, 400, 'Rota /cleanup só aceita método DELETE.', null);
            }
            break;
        default:
            $response_builder = ResponseHelper::builder(true, 200, 'Rota inválida.', null);
            break;
    }

    
    $response_builder->addAdditionalField('elapsed_millis_in_server', round((microtime(true) - $start_time) * 1000, 3));
    $response_builder->filterFields();
    ResponseHelper::buildAndRespond($response_builder);
    
?> 