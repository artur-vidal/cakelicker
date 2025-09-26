<?php

    require_once __DIR__ . '\\..\\controllers\\UserController.php';

    $u_controller = new UserController($conn);
    $response = null; // resposta da API, echo no fim do arquivo
    $identifier_presets = ['first', 'last'];

    // convertendo preset caso encontre
    if(in_array($id, $identifier_presets)) {
        $new_id = $id;

        switch($id) {
            case 'first':
                $new_id = $u_controller->getFirstUserId();
                break;

            case 'last':
                $new_id = $u_controller->getLastUserId();
                break;
        }

        $id = $new_id;
    }


    switch($method) {
        case 'GET':

            if($id == null) {

                // pegando parâmetros pra usar
                $page = $_GET['page'] ?? 1;
                $limit = $_GET['limit'] ?? 10;
                $offset = $_GET['offset'] ?? 0;
                $sort = $_GET['sort'] ?? 'id';
                $direction = $_GET['direction'] ?? 'ASC';

                $response = $u_controller->getUsers($page, $offset, $limit, $sort, strtoupper($direction));

            } else {

                $response = $u_controller->getUser($id);

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

        case 'PATCH':

            $response = $u_controller->updateUserPartial($id, $data);
            break;
    }

    // enviando resposta
    echo json_encode($response);

?>