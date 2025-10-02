<?php

    $s_controller = new Cakelicker\Controllers\SaveController($conn);
    $save_id = $endpoint_parts[3] ?? null;

    switch($method) {
        case 'GET':
            if($save_id)
                $response_builder = $s_controller->getSave($user_id, $save_id);
            else
                $response_builder = $s_controller->getSaves($user_id);
            break;
    }

?>