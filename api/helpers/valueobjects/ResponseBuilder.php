<?php

    namespace Cakelicker\ValueObjects;
    use Cakelicker\Helpers\ArrayHelper;
    use Cakelicker\ValueObjects\Response;

    class ResponseBuilder {

        private $success;
        private $code;
        private $message = null;
        private $debugMessage = null;
        private $data = null;
        private $headers = null;
        private $additionalFields = null;

        public function __construct($success, $code) {
            $this->success = $success;
            $this->code = $code;
        }

        public function build() {
            if(!IS_LOCAL)
                $this->eraseSensitiveInfo();
            
            return new Response(
                $this->success,
                $this->code,
                $this->message,
                $this->debugMessage,
                $this->data,
                $this->headers,
                $this->additionalFields
            );
        }

        public function setMessage($message) {
            $this->message = $message;
            return $this;
        }

        public function setDebugMessage($debug_message) {
            $this->debugMessage = $debug_message;
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
            $this->additionalFields[$field_name] = $field_value;
            return $this;
        }

        public function eraseSensitiveInfo() {
            $this->debugMessage = null;

            if(isset($this->additionalFields['caller_origin']))
                unset($this->additionalFields['caller_origin']);

            if(isset($this->additionalFields['line_called']))
                unset($this->additionalFields['line_called']);

            return $this;
        }

        public function filterFields() {
            if(isset($_GET['fields'])) {
                $field_array = array_filter(explode(',', $_GET['fields']));

                $filter = function($array) use ($field_array) {
                    return ArrayHelper::filterArrayKeys($field_array, $array);
                };

                if(array_is_list($this->data))
                    for($i = 0; $i < count($this->data); $i++) {
                        $this->data[$i] = $filter($this->data[$i]);
                    }
                else
                    $this->data = $filter($this->data);
            }
        }

    }

?>
