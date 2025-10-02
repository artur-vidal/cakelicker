<?php

    namespace Cakelicker\Helpers;

    class ResponseHelper {
        public static function generate($success, $code, $message, $debug_message, $data = null) {
            $debug_info = debug_backtrace();
        
            http_response_code($code);
            $generated = [
                'caller_origin' => $debug_info[0]['file'],
                'line_called' => $debug_info[0]['line'],
                'success' => $success,
                'message' => $message,
                'debug_message' => $debug_message,
                'data' => $data
            ];

            if(!IS_LOCAL) {
                unset($generated['caller_origin']);
                unset($generated['line_called']);
                unset($generated['debug_message']);
            }

            return $generated;
        }

        public static function respond($response) {
            echo json_encode($response);
            exit;
        }
    }


?>