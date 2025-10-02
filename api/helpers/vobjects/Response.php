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

        public function __construct($success, $status_code, $message, $debug_message, $data) {
            $this->success = $success;
            $this->code = $status_code;
            $this->message = $message;
            $this->debug_message = $debug_message;
            $this->data = $data;

            $this->headers = [];
            $this->additional_fields = [];
        }

        public function addHeader($header_name, $header_value) {
            $this->headers[$header_name] = $header_value;
        }

        public function getHeadersAsStrings() {
            $header_array = [];
            foreach($this->headers as $field => $value) {
                $header_array[] = "$field:$value";
            }
            return $header_array;
        }

        public function addAdditionalField($field_name, $field_value) {
            $this->additional_fields[$field_name] = $field_value;
        }

        public function eraseSensitiveInfo() {
            $this->debug_message = false;
        }

        public function getResponseAsAssocArray() {
            $response_array = [];

            $response_array['success'] = $this->success;

            foreach($this->additional_fields as $field => $value) {
                $response_array[$field] = $value;
            }

            $response_array['message'] = $this->message;
            $response_array['debug_message'] = $this->debug_message;
            $response_array['data'] = $this->data;

            return $response_array;
        }

        public function getCode() {
            return $this->code;
        }
    }


?>