<?php

    $s_controller = new Cakelicker\Controllers\SaveController($conn);
    $save_id = $endpoint_parts[3] ?? null;

    switch($method) {
        case 'GET':
            if($save_id)
                $response = $s_controller->getSave($user_id, $save_id);
            else
                $response = $s_controller->getSaves($user_id);
            break;
    }

?>