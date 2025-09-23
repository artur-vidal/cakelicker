<?php

    function generate_response($success, $code, $message, $data = null) {
        $debug_info = debug_backtrace();
        
        http_response_code($code);
        $generated = [
            'caller_origin' => $debug_info[0]['file'],
            'line_called' => $debug_info[0]['line'],
            'status' => ($success) ? 'success' : 'failure',
            'message' => $message,
            'data' => $data
        ];

        if(!IS_LOCAL) {
            unset($generated['caller_origin']);
            unset($generated['line_called']);
            unset($generated['message']);
        }

        return $generated;
    }

    function array_has_keys($keys, $array) {

        for($i = 0; $i < count($keys); $i++) {
            
            if(!isset($array[$keys[$i]])) {
                return false;
            }

        }

        return true;

    }

    function filter_array_keys($keys, $array) {
        $new_arr = [];

        for($i = 0; $i < count($keys); $i++) {
            $cur_key = $keys[$i];

            if(isset($array[$cur_key])){
                $new_arr[$cur_key] = $array[$cur_key];
            }
        }

        return $new_arr;
    }

    function remove_useless_saves($dbconn) {

        // reunindo saves inutilizados
        try {
            $save_query = $dbconn->prepare('SELECT savepath FROM saves');
            $save_query->execute();

            $savelist = $save_query->fetchAll(PDO::FETCH_COLUMN);
            
            $dir_list = scandir(UPLOAD_DIR);
            
            for($i = 0; $i < count($dir_list); $i++) {
                $cur_element = $dir_list[$i];

                if(!is_file(UPLOAD_DIR . $cur_element) or $cur_element === '.' or $cur_element === '..') {
                    continue;
                }

                if(!in_array($cur_element, $savelist)) {
                    @unlink(UPLOAD_DIR . $cur_element);
                }
            }

        } catch(Exception $err) {
            // ignorando caso não dê certo
        }

    }

    function filter_response_data($field_array, $response_data) {
        $new_data = [];

        if(array_is_list($response_data)) {

            for($i = 0; $i < count($response_data); $i++) {
                $new_data[] = filter_array_keys($field_array, $response_data[$i]);
            }

        } else {
            $new_data = filter_array_keys($field_array, $response_data);
        }

        return $new_data;
    }
?>