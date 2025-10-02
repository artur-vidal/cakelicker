<?php

    namespace Cakelicker\Helpers;

    use Cakelicker\ValueObjects\Response;

    class ResponseHelper {
        
        public static function generate($success, $code, $message, $debug_message, $data = null) {
            $response = new Response(
                $success,
                $code,
                $message,
                $debug_message,
                $data
            );

            $response->addHeader('Content-Type', 'application/json');

            $debug_info = debug_backtrace();
            $response->addAdditionalField('caller_origin', $debug_info[0]['file']);
            $response->addAdditionalField('line_called', $debug_info[0]['line']);

            if(!IS_LOCAL)
                $response->eraseSensitiveInfo();

            return $response;
        }

        public static function respond($response_object) {
            $headers = $response_object->getHeadersAsStrings();
            for($i = 0; $i < count($headers); $i++) {
                header($headers[$i]);
            }

            http_response_code($response_object->getCode());
            echo json_encode($response_object->getResponseAsAssocArray());
            exit;
        }

    }


?>