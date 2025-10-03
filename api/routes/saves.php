<?php

    $s_controller = new Cakelicker\Controllers\SaveController($conn);
    $save_id = $endpoint_parts[3] ?? null;

    switch($method) {
        case 'GET':
            $response_builder = $s_controller->getSavesOfUser($user_id);
            break;
            
        case 'POST':
            $response_builder = $s_controller->createSave($user_id, $data);
            break;
    }

?>