<?php

    namespace Cakelicker\Traits;
    use Cakelicker\Helpers\ResponseHelper;
    
    trait ValidationTraits {

        protected function validateUsername($username) {
            // deve ter até 20 caracteres minusculos, numeros, sem espaço, e apenas _ como caractere especial
            return preg_match('/^[a-z0-9_]{4,20}$/', $username);
        }

        protected function validateNickname($nickname) {
            return is_string($nickname) and strlen($nickname) <= 255;
        }

        protected function validatePassword($password) {
            // pelo menos 8 caracteres e com número
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
        }

        protected function validateEmail($email) {
            // exemplo@email.com
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }

        protected function validateBirthdate($birthdate) {
            // formato YYYY-MM-DD, se não passar direto eu já retorno
            if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
                return false;
            }

            // se a data não existir, como 30/02, também retorno
            $date_parts = array_filter(explode('-', $birthdate)); // separando partes da data
            if(!checkdate((int) $date_parts[1], (int) $date_parts[2], (int) $date_parts[0])) {
                return false;
            }

            $birthtime = strtotime($birthdate);
            $future = $birthtime > time();
            $past = $birthtime < strtotime('1900-01-01');

            return !$future && !$past;
        }

        protected function validateId($id) {
            return (ctype_digit($id) || is_int($id)) && $id > 0;
        }

        protected function validateIdentifier($identifier) {
            return $this->validateId($identifier) || $this->validateUsername($identifier);
        }

    }

?>