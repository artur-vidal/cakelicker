<?php

    require_once __DIR__ . '\\..\\controllers\\UserController.php';

    $u_controller = new UserController($conn);
    $user_id = $endpoint_parts[1] ?? null;
    $subresource = $endpoint_parts[2] ?? null;

    // verificando se tem algum subrecurso
    if($subresource) {

        // quebrando se não houver ID - subrecursos precisam de um
        if(!$user_id) {
            respond(generate_response(false, 400, 'Sub-recursos precisam de especificação de identificador (ID ou username).', null));
        }

        switch($subresource) {
            case 'saves':
                include __DIR__ . '\\saves.php';
                break;
            default:
                $response = generate_response(false, 404, 'Sub-recurso não existe.', null);
                break;
        }

    } else {

        // put e delete não devem aceitar presets, então nego logo aqui caso o endpoint seja um
        if(($method == 'PUT' || $method == 'DELETE') && in_array($id, USER_ENDPOINT_PRESETS)) {

            respond(generate_response(false, 400, 'Não é possível usar presets com PUT ou DELETE.', null));

        }


        switch($method) {
            case 'GET':
                if($user_id == null) {

                    // pegando parâmetros pra usar
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $offset = $_GET['offset'] ?? 0;
                    $sort = $_GET['sortby'] ?? 'id';
                    $direction = $_GET['direction'] ?? 'ASC';

                    $response = $u_controller->getUsers($page, $offset, $limit, $sort, strtoupper($direction));

                } else {

                    $response = $u_controller->getUser($user_id);

                }

                // filtrando com o parâmetro 'fields'
                if(isset($_GET['fields'])) {
                    $fields = array_filter(explode(',', $_GET['fields']));
                    $response['data'] = filter_response_data($fields, $response['data']);
                }
                break;
            
            case 'POST':
                $response = $u_controller->createUser($data);
                break;

            case 'PUT':
                $response = $u_controller->updateUser($user_id, $data);
                break;
            
            case 'DELETE':
                $response = $u_controller->deleteUser($user_id);
                break;

            default:
                $response = generate_response(false, 405, 'Método não permitido para esse recurso', 'Métodos permitidos: GET, POST, PUT, DELETE', $method);
                break;
        }

    }

?>