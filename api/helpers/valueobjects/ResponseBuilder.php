<?php

    namespace Cakelicker\ValueObjects;
    use Cakelicker\ValueObjects\Response;

    class ResponseBuilder {

        private $success;
        private $code;
        private $message = null;
        private $debug_message = null;
        private $data = null;
        private $headers = null;
        private $additional_fields = null;

        public function __construct($success, $code) {
            $this->success = $success;
            $this->code = $code;
        }

        public function build() {
            return new Response(
                $this->success,
                $this->code,
                $this->message,
                $this->debug_message,
                $this->data,
                $this->headers,
                $this->additional_fields
            );
        }

        public function setMessage($message) {
            $this->message = $message;
            return $this;
        }

        public function setDebugMessage($debug_message) {
            $this->debug_message = $debug_message;
            return $this;
        }

        public function setData($data) {
            $this->data = $data;
            return $this;
        }

        public function addHeader($header_name, $header_value) {
            $this->headers[$header_name] = $header_value;
            return $this;
        }

        public function addAdditionalField($field_name, $field_value) {
            $this->additional_fields[$field_name] = $field_value;
            return $this;
        }

        public function eraseSensitiveInfo() {
            $this->debug_message = false;
            return $this;
        }

    }

?>
