<?php

    namespace Cakelicker\InputObjects;
    
    class UserInput extends InputObject {

        protected $username;
        protected $nickname;
        protected $email;
        protected $password;
        protected $birthdate;

        public function fillFromAssocArray($array) {
            $this->withUsername($array['username'] ?? null)
                ->withNickname($array['nickname'] ?? null)
                ->withEmail($array['email'] ?? null)
                ->withHashedPassword($array['password'] ?? null)
                ->withBirthdate($array['birthdate'] ?? null);
        }

        public function withUsername($username) {
            $this->setIfPresentAndValid($username, 'username', 'validateUsername', 'Nome de usuário deve ter de 4 a 20 caracteres, apenas letras minúsculas, números ou underline.');
            return $this;
        }

        public function withNickname($nickname) {
            $this->setIfPresentAndValid($nickname, 'nickname', 'validateNickname', 'Nickname não é string ou é grande demais (255+ caracteres)');
            return $this;
        }

        public function withEmail($email) {
            $this->setIfPresentAndValid($email, 'email', 'validateEmail', 'E-mail deve seguir formato padrão exemplo@dominio.com');
            return $this;
        }

        public function withHashedPassword($password) {
            $this->setIfPresentAndValid($password, 'password', 'validatePassword', 'Senha deve ter pelo menos 8 caracteres, uma letra maiúscula e um número.');
            $this->password = password_hash($password, PASSWORD_BCRYPT);
            return $this;
        }

        public function withBirthdate($birthdate) {
            $this->setIfPresentAndValid($birthdate, 'birthdate', 'validateBirthdate', 'Data de nascimento tem que estar em formato YYYY-MM-DD, não ser futura e estar depois de 1900-01-01.');
            return $this;
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

        public function toAssocArray() {
            $return_array = [
                'username' => $this->getUsername(),
                'nickname' => $this->getNickname(),
                'email' => $this->getEmail(),
                'password' => $this->getHashedPassword(),
                'birthdate' => $this->getBirthdate()
            ];
            return $return_array;
        }

        public function isComplete() {
            return $this->countSetFields() === 5;
        }

        public function isEmpty() {
            return $this->countSetFields() === 0;
        }
        
    }


?>