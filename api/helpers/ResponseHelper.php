<?php

    namespace Cakelicker\Helpers;
    use Cakelicker\ValueObjects\ResponseBuilder;

    class ResponseHelper {
        
        public static function builder($success, $code, $message, $debug_message, $data = null) {
            $debug_info = debug_backtrace();

            $responseBuilder = new ResponseBuilder(
                $success,
                $code
            );

            $responseBuilder->setMessage($message)
                ->setDebugMessage($debug_message)
                ->setData($data)
                ->addHeader('Content-Type', 'application/json')
                ->addAdditionalField('caller_origin', $debug_info[0]['file'])
                ->addAdditionalField('line_called', $debug_info[0]['line']);

            return $responseBuilder;
        }

        public static function buildAndRespond($response_builder) {
            self::respond($response_builder->build());
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