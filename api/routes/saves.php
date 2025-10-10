<?php

    use Cakelicker\Helpers\ResponseHelper;

    $s_controller = new Cakelicker\Controllers\SaveController($conn);

    if($subresource === null) {
        // rota /saves
    } else {
        switch($method) {
            case 'GET':
                $response_builder = $s_controller->getSavesOfUser($resource_id);
                break;
                
            case 'POST':
                $response_builder = $s_controller->createSave($resource_id, $data);
                break;

            default:
                $response_builder = ResponseHelper::builder(false, 405, 'Método não permitido para esse recurso', 'Métodos permitidos: GET, POST', $method);
                break;
        }
    }
?>