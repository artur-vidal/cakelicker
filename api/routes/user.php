<?php

    require_once './controllers/UserController.php';

    $u_controller = new UserController($conn);
    $response = null; // resposta da API, echo no fim do arquivo

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

                if($id < 1) {
                    $response = generate_response(false, 400, 'id inválido');
                }

                if(ctype_digit($id)) { 

                   $response = $u_controller->getUserById($id);

                } else {

                    $preset_id = $id;

                    if($id == 'first') $preset_id = $u_controller->getFirstUserId();
                    else if($id == 'last') $preset_id = $u_controller->getLastUserId();

                    if($preset_id == null) {
                        $response = generate_response(false, 404, 'usuários não encontrados');
                        break;
                    }
                    
                    if (is_int($preset_id)) {
                        $response = $u_controller->getUserById($preset_id);
                    } else {
                        $response = $u_controller->getUserByUsername($preset_id);
                    }
                }

            }

            // filtrando com o parâmetro 'fields'
            if(isset($_GET['fields'])) {
                $fields = array_filter(explode(',', $_GET['fields']));
                $response['data'] = filter_response_data($fields, $response['data']);
            }

            break;
        
        case 'POST':

            $columns = ['username', 'nickname', 'email', 'password', 'birthdate'];
            if(array_has_keys($columns, $data)){

                $response = $u_controller->createUser($data['username'], $data['nickname'], $data['email'], $data['password'], $data['birthdate']);

            } else {

                $response = generate_response(false, 400, 'dados insuficientes para registro');

            }

            break;

        case 'PATCH':

            $response = $u_controller->updateUserPartial($id, $data);
            break;
    }

    // enviando resposta
    echo json_encode($response);

?>