<?php

    namespace Cakelicker\ValueObjects;

    use Cakelicker\Traits\ValidationTraits;

    use Exception;

    class User {

        use ValidationTraits;

        private $username;
        private $nickname;
        private $email;
        private $password;
        private $birthdate;

        public function __construct($username = null, $nickname = null, $email = null, $password = null, $birthdate = null) {
            $this->setIfPresentAndValid($username, 'username', 'validateUsername', 'Nome de usuário deve ter de 4 a 20 caracteres, apenas letras minúsculas, números ou underline.');
            $this->setIfPresentAndValid($nickname, 'nickname', 'validateNickname', 'Nickname não é string ou é grande demais (255+ caracteres)');
            $this->setIfPresentAndValid($email, 'email', 'validateEmail', 'E-mail deve seguir formato padrão exemplo@dominio.com');
            $this->setIfPresentAndValid($password, 'password', 'validatePassword', 'Senha deve ter pelo menos 8 caracteres, uma letra maiúscula e um número.');
            $this->setIfPresentAndValid($birthdate, 'birthdate', 'validateBirthdate', 'Data de nascimento tem que estar em formato YYYY-MM-DD, não ser futura e estar depois de 1900-01-01.');

            if($password !== null)
                $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        }

        private function setIfPresentAndValid($value, $propertyName, $validationMethod, $exceptionMessage) {
            if($value !== null) {
                if(!$this->$validationMethod($value))
                    throw new Exception($exceptionMessage, 400);

                $this->$propertyName = $value;
            }
        }

        public function toAssocArray() {
            $return_array = array_filter([
                'username' => $this->getUsername(),
                'nickname' => $this->getNickname(),
                'email' => $this->getEmail(),
                'password' => $this->getHashedPassword(),
                'birthdate' => $this->getBirthdate()
            ], fn($value) => $value !== null);
            return $return_array;
        }

        public function getUsername() {
            return $this->username;
        }

        public function getNickname() {
            return $this->nickname;
        }

        public function getEmail() {
            return $this->email;
        }
        public function getHashedPassword() {
            return $this->password;
        }

        public function getBirthdate() {
            return $this->birthdate;
        }
    }


?>