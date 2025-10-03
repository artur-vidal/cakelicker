<?php

    namespace Cakelicker\ValueObjects;

    class Response {

        private $success;
        private $code;
        private $message;
        private $debug_message;
        private $data;
        private $headers;
        private $additional_fields;

        public function __construct($success, $status_code, $message, $debug_message, $data, $headers, $additional_fields) {
            $this->success = $success;
            $this->code = $status_code;
            $this->message = $message;
            $this->debug_message = $debug_message;
            $this->data = $data;
            $this->headers = $headers;
            $this->additional_fields = $additional_fields;
        }

        public function getHeadersAsStrings() {
            $header_array = [];
            foreach($this->headers as $field => $value) {
                $header_array[] = "$field:$value";
            }
            return $header_array;
        }

        public function getResponseAsAssocArray() {
            $responseBuilder_array = [];

            $responseBuilder_array['success'] = $this->success;

            foreach($this->additional_fields as $field => $value) {
                $responseBuilder_array[$field] = $value;
            }

            $responseBuilder_array['message'] = $this->message;
            $responseBuilder_array['debug_message'] = $this->debug_message;
            $responseBuilder_array['data'] = $this->data;

            return $responseBuilder_array;
        }

        public function getCode() {
            return $this->code;
        }
    }


?>