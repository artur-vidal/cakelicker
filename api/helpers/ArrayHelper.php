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
        
    }
?>