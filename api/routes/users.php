<?php

    use Cakelicker\Helpers\ResponseHelper;

    $user_controller = new Cakelicker\Controllers\UserController($conn);
    $user_id = $endpoint_parts[1] ?? null;
    $subresource = $endpoint_parts[2] ?? null;

    if($subresource) {
        if(!$user_id) {
            $error_response = ResponseHelper::generate(false, 400, 'Sub-recursos precisam de especificação de identificador (ID ou username).', null);
            ResponseHelper::respond($error_response);
        }

        switch($subresource) {
            case 'saves':
                require_once __DIR__ . '\\saves.php';
                break;
            default:
                $response = ResponseHelper::generate(false, 404, 'Sub-recurso não existe.', null);
                break;
        }
    } else {
        if(($method == 'PUT' || $method == 'DELETE') && $user_controller->isPreset($user_id)) {
            $error_response = ResponseHelper::generate(false, 400, 'Não é possível usar presets com PUT ou DELETE.', null);
            ResponseHelper::respond($error_response);
        }

        switch($method) {
            case 'GET':
                if($user_id == null) {
                    $page = $_GET['page'] ?? 1;
                    $limit = $_GET['limit'] ?? 10;
                    $offset = $_GET['offset'] ?? 0;
                    $sort = $_GET['sortby'] ?? 'id';
                    $direction = $_GET['direction'] ?? 'ASC';

                    $response = $user_controller->getPagedUsers($page, $offset, $limit, $sort, strtoupper($direction));
                } else {
                    $response = $user_controller->getUser($user_id);
                }

                if(isset($_GET['fields'])) {
                    $fields = array_filter(explode(',', $_GET['fields']));
                    $response['data'] = ArrayHelper::filterArrayKeys($fields, $response['data']);
                }
                break;
            
            case 'POST':
                $response = $user_controller->createUser($data);
                break;

            case 'PUT':
                $response = $user_controller->updateUser($user_id, $data);
                break;
            
            case 'DELETE':
                $response = $user_controller->deleteUser($user_id);
                break;

            default:
                $response = ResponseHelper::generate(false, 405, 'Método não permitido para esse recurso', 'Métodos permitidos: GET, POST, PUT, DELETE', $method);
                break;
        }

    }

?>