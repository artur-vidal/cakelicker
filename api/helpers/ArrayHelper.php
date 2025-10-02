<?php

    namespace Cakelicker\Helpers;

    class ArrayHelper {

        public static function arrayHasKeys($keys, $array) {
            for($i = 0; $i < count($keys); $i++) {
                
                if(!isset($array[$keys[$i]])) {
                    return false;
                }

            }

            return true;
        }

        public static function filterArrayKeys($keys, $array) {
            $new_arr = [];

            for($i = 0; $i < count($keys); $i++) {
                $cur_key = $keys[$i];

                if(isset($array[$cur_key])){
                    $new_arr[$cur_key] = $array[$cur_key];
                }
            }

            return $new_arr;
        }

        public static function filterResponseData($new_fields, $response) {
            $new_data = [];

            if(array_is_list($response['data'])) {

                for($i = 0; $i < count($response['data']); $i++) {
                    $new_data[] = self::filterArrayKeys($field_array, $response['data'][$i]);
                }

            } else {
                $new_data = self::filterArrayKeys($field_array, $response['data']);
            }

            return $new_data;
        }
    }
?>