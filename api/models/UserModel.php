<?php

    namespace Cakelicker\Models;
    use Cakelicker\Traits\ValidationTraits;

    class UserModel {

        use ValidationTraits;

        private $conn;
        private $querySelectColumns;
        private $querySelectColumnsAsString;

        public function __construct($dbconn) {
            $this->conn = $dbconn;
            $this->querySelectColumns = ['id', 'username', 'nickname', 'email', 'birthdate', 'creationdate'];
            $this->querySelectColumnsAsString = implode(', ', $this->querySelectColumns);
        }

        public function getUser($identifier) {
            try {
                if ($this->validateId($identifier)) {
                    $get_statement = $this->conn->prepare('SELECT ' . $this->querySelectColumnsAsString . ' FROM users WHERE id = :id');
                    $get_statement->execute(['id' => (int)$identifier]);
                } else {
                    $get_statement = $this->conn->prepare('SELECT ' . $this->querySelectColumnsAsString . ' FROM users WHERE username = :username');
                    $get_statement->execute(['username' => $identifier]);
                }

                $user_found = $get_statement->fetch(\PDO::FETCH_ASSOC);
                return $user_found;
            } catch(\PDOException $err) {
                throw $err;
            }
        }

        public function getPagedUsers($paginationParamsObject) {
            try {
                $order_param = $paginationParamsObject->getSortingColumn();
                $order_direction = $paginationParamsObject->getSortingDirection();
                $first_index = $paginationParamsObject->getFirstIndex();
                $per_page = $paginationParamsObject->getPerPageLimit();

                $get_users_query = "SELECT $this->querySelectColumnsAsString FROM users ORDER BY $order_param $order_direction LIMIT :lim OFFSET :off";
                
                $get_statement = $this->conn->prepare($get_users_query);
                $get_statement->bindValue(':off', $first_index, \PDO::PARAM_INT);
                $get_statement->bindValue(':lim', $per_page, \PDO::PARAM_INT);
                $get_statement->execute();

                $users_found = $get_statement->fetchAll(\PDO::FETCH_ASSOC);
                return $users_found;
            } catch(\PDOException $err) {
                throw $err;
            }
        }

        public function createUserAndGetId($user_object) {
            try {
                $this->conn->beginTransaction();

                if($this->isDuplicateUser($user_object)) {
                    $this->conn->rollBack();
                    throw new \Exception('Já existe um usuário com esse username ou email.', 409);
                }

                $params = $user_object->toAssocArray();

                $user_statement = $this->conn->prepare('INSERT INTO users(username, email, password, nickname, birthdate) VALUES(:username, :email, :password, :nickname, :birthdate)');
                $user_statement->execute($params);

                $created_user_id = $this->conn->lastInsertId();

                $this->conn->commit();

                return $created_user_id;
            } catch(\Exception $err) {
                $this->conn->rollBack();
                throw $err;
            }
        }

        public function updateUserAndReturn($identifier, $user_object) {
            try {
                $this->updateUser($identifier, $user_object);
                return $this->getUser($identifier);
            } catch(\Exception $err) {
                throw $err;
            }
        }

        public function updateUser($identifier, $user_object) {
            if(!$this->validateIdentifier($identifier)) {
                throw new \Exception('Identificador inválido.', 400);
            }

            $fields_to_update = $user_object->toAssocArray();

            $update_clauses = [];
            $params = [];
            foreach($fields_to_update as $field => $value) {
                $update_clauses[] = "$field = :$field";
                $params[$field] = $value;
            }

            $where_clause = '';
            if($this->validateId($identifier)) {
                $where_clause = 'id = :identifier';
                $params['identifier'] = (int)$identifier;
            } else {
                $where_clause = 'username = :identifier';
                $params['identifier'] = $identifier;
            }

            try {
                $this->conn->beginTransaction();
                
                $set_clauses = implode(', ', $update_clauses);
                $query = "UPDATE users SET $set_clauses WHERE $where_clause";

                $update_statement = $this->conn->prepare($query);
                $update_statement->execute($params);

                $this->conn->commit();
            } catch(\PDOException $err) {
                $this->conn->rollBack();
                throw $err;
            }
        }

        public function deleteUser($identifier) {
            if(!$this->validateIdentifier($identifier)) {
                throw new \Exception('Identificador inválido.', 400);
            }

            $where_clause = '';
            $params = [];
            if($this->validateId($identifier)) {
                $where_clause = 'id = :identifier';
                $params['identifier'] = (int)$identifier;
            } else {
                $where_clause = 'username = :identifier';
                $params['identifier'] = $identifier;
            }

            try {
                $this->conn->beginTransaction();

                $delete_statememt = $this->conn->prepare('DELETE FROM users WHERE '. $where_clause);
                $delete_statememt->execute($params);

                if($delete_statememt->rowCount() == 0) 
                    throw new \Exception('Nenhum usuário encontrado para remoção.', 404);

                $this->conn->commit();
            } catch(\PDOException $err) {
                $this->rollBack();
                throw $err;
            }
        }

        public function usernameToId($username) {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users WHERE username = :username');
                $id_query->execute(['username' => $username]);

                $found_id = $id_query->fetch(\PDO::FETCH_COLUMN);
                
                return ($found_id) ? (int)$found_id : null;
            } catch(\PDOException $err) {
                return null;
            }
        }

        private function isDuplicateUser($user_object) {
            $same_user_query = $this->conn->prepare('SELECT 1 FROM users WHERE username = :username OR email = :email LIMIT 1');
            $same_user_query->execute([
                'username' => $user_object->getUsername(),
                'email' => $user_object->getEmail()
            ]);

            return $same_user_query->fetch(\PDO::FETCH_COLUMN) != 0;
        }

        public function getFirstUserId() {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users ORDER BY id ASC LIMIT 1');
                $id_query->execute();

                $val = $id_query->fetch(\PDO::FETCH_COLUMN);

                return $val;

            } catch(\PDOException $err) {
                throw $err;
            }
        }

        public function getLastUserId() {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users ORDER BY id DESC LIMIT 1');
                $id_query->execute();

                $val = $id_query->fetch(\PDO::FETCH_COLUMN);

                return $val;

            } catch(\PDOException $err) {
                throw $err;
            }
        }

    }
?>