<?php

    require_once __DIR__ . '\\init.php';
    
    // pegando dados da requisição
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $_SERVER['REQUEST_METHOD'];

    // pegando recurso e id da uri
    $uri_parts = parse_url($_SERVER['REQUEST_URI']);
    $uri_parts = array_filter(explode('/', $uri_parts['path']), function($el) {
        return $el != '';
    });

    // pegando pedaços principais da uri
    $primary_resource = $uri_parts[3] ?? null;
    $id = $uri_parts[4] ?? null;

    // mandando pras rotas
    switch($primary_resource) {
        case 'users':
            include __DIR__ . '\\routes\\user.php';
            break;
        default:
            echo json_encode(generate_response(true, 200, 'Rota inválida.', 'Essa rota não consta no switch'));
            break;
    }
?>