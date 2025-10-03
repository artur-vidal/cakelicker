<?php

    namespace Cakelicker\ValueObjects;
    use Cakelicker\Traits\ValidationTraits;
    use Cakelicker\ValueObjects\User;

    class UserBuilder {

        use ValidationTraits;

        private $username = null;
        private $nickname = null;
        private $email = null;
        private $password = null;
        private $birthdate = null;

        public function fillFromAssocArrayAndBuild($array) {
            $this->fillFromAssocArray($array);
            return $this->build();
        }

        public function fillFromAssocArray($array) {
            $this->withUsername($array['username'] ?? null)
                ->withNickname($array['nickname'] ?? null)
                ->withEmail($array['email'] ?? null)
                ->withPassword($array['password'] ?? null)
                ->withBirthdate($array['birthdate'] ?? null);
        }

        public function build() {
            if($this->countSetFields() == 0) {
                throw new \Exception('Nenhuma informação fornecida para criar ou atualizar usuário.', 400);
            }

            return new User(
                $this->username, 
                $this->nickname, 
                $this->email, 
                $this->password,
                $this->birthdate
            );
        }

        public function withUsername($username) {
            $this->username = $username;
            return $this;
        }

        public function withNickname($nickname) {
            $this->nickname = $nickname;
            return $this;
        }

        public function withEmail($email) {
            $this->email = $email;
            return $this;
        }

        public function withPassword($password) {
            $this->password = $password;
            return $this;
        }

        public function withBirthdate($birthdate) {
            $this->birthdate = $birthdate;
            return $this;
        }

        public function isComplete() {
            return $this->countSetFields() == 5;
        }

        public function isEmpty() {
            return $this->countSetFields() == 0;
        }

        private function countSetFields() {
            $fields = [
                'username' => $this->username, 
                'nickname' => $this->nickname, 
                'email' => $this->email, 
                'password' => $this->password,
                'birthdate' => $this->birthdate
            ];
            $set_fields = array_filter($fields, fn($value) => $value !== null);
            return count($set_fields);
        }

    }


?>