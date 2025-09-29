<?php

    require_once __DIR__ . '\\init.php';
    
    // pegando dados da requisição
    $data = json_decode(file_get_contents('php://input'), true);
    $method = $_SERVER['REQUEST_METHOD'];
    $response = null; // resposta da API, echo no fim do arquivo

    // pegando recurso e id da uri
    $uri_parts = parse_url($_SERVER['REQUEST_URI']);

    // pegando pedaços principais da uri
    $endpoint_parts = array_slice(explode('/', $uri_parts['path']), 3) ?? null;


    // respondo logo se não tiver rota
    if(!$endpoint_parts || $endpoint_parts[0] == '') {

        respond(generate_response(true, 200, 'Sem rota', null));

    }

    // mandando pras rotas
    switch($endpoint_parts[0]) {
        case 'users':
            include __DIR__ . '\\routes\\users.php';
            break;
        case 'login':
            include __DIR__ . '\\routes\\login.php';
            break;
        default:
            $response = generate_response(true, 200, 'Rota inválida.', null);
            break;
    }


    // enviando resposta
    respond($response);
?>