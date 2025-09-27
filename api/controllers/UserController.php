<?php

    require_once __DIR__ . '\\..\\init.php';

    class UserController {
        private $conn;

        public function __construct($dbconn) {
            // conectando com o banco
            $this->conn = $dbconn;

            // garantindo que a pasta de saves exista
            if(!is_dir(UPLOAD_DIR)){
                mkdir(UPLOAD_DIR);
            }
        }

        private function validateUsername($username) {
            // deve ter até 20 caracteres minusculos, numeros, sem espaço, e apenas _ como caractere especial
            return preg_match('/^[a-z0-9_]{4,20}$/', $username);
        }

        private function validatePassword($password) {
            // pelo menos 8 caracteres e com número
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
        }

        private function validateEmail($email) {
            // exemplo@email.com
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }

        private function validateBirthdate($birthdate) {
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

        private function validateId($id) {
            return (ctype_digit($id) || is_int($id)) && $id > 0;
        }

        private function validateIdentifier($identifier) {
            return $this->validateId($identifier) || $this->validateUsername($identifier);
        }

        private function searchDuplicateUser($username, $email) {
            $same_user_query = $this->conn->prepare('SELECT 1 FROM users WHERE username = :username OR email = :email LIMIT 1');
            $same_user_query->execute(['username' => $username, 'email' => $email]);

            return $same_user_query->fetch(PDO::FETCH_COLUMN) != 0;
        }

        public function getFirstUserId() {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users ORDER BY id ASC LIMIT 1');
                $id_query->execute();

                $val = $id_query->fetch(PDO::FETCH_COLUMN);

                return ($val) ? $val : null;

            } catch(PDOException $err) {
                echo json_encode(generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage()));
                exit;
            }
        }

        public function getLastUserId() {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users ORDER BY id DESC LIMIT 1');
                $id_query->execute();

                $val = $id_query->fetch(PDO::FETCH_COLUMN);

                return ($val) ? $val : null;

            } catch(PDOException $err) {
                echo json_encode(generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage()));
                exit;
            }
        }

        public function getUser($identifier) {

            try {

                $query_first_part = 'SELECT id, username, nickname, email, birthdate, creationdate FROM users WHERE ';
                if ($this->validateId($identifier)) {
                    $stmt = $this->conn->prepare($query_first_part . 'id = :id');
                    $stmt->execute(['id' => (int)$identifier]);
                } else {
                    $stmt = $this->conn->prepare($query_first_part .  'username = :username');
                    $stmt->execute(['username' => $identifier]);
                }

                $user_found = $stmt->fetch(PDO::FETCH_ASSOC);

                if($user_found){
                    return generate_response(true, 200, 'Usuário encontrado.', null, $user_found);
                } else {
                    return generate_response(false, 404, 'Não existe usuário com esse identificador.', null, $identifier);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage(), $identifier);
            }
        }

        public function getUserBySession($session_token) {

            // a desenvolver

        }

        public function getUsers($page, $offset, $per_page, $order_param, $order_direction) {

            // restringindo parâmetros permitidos por segurança
            $column_whitelist = ['id', 'username', 'email', 'nickname', 'birthdate', 'creationdate'];
            $order_direction_whitelist = ['ASC', 'DESC'];

            // garantindo que os parametro estejam em limites razoáveis
            $page = max(1, (int)$page);
            $offset = max(0, (int)$offset);
            $per_page = min(max(1, (int)$per_page), 150);

            if(!in_array($order_param, $column_whitelist)) $order_param = 'id';
            if(!in_array(strtoupper($order_direction), $order_direction_whitelist)) $order_direction = 'ASC';

            $first_page = $per_page * ($page - 1) + $offset;

            try {
                $stmt = $this->conn->prepare("SELECT id, username, nickname, email, birthdate, creationdate FROM users ORDER BY $order_param $order_direction LIMIT :lim OFFSET :off");
                $stmt->bindValue(':off', $first_page, PDO::PARAM_INT);
                $stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
                $stmt->execute();

                $users_found = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($users_found){
                    return generate_response(true, 200, 'Usuários encontrados.', $users_found);
                } else {
                    return generate_response(false, 404, 'Usuários não foram encontrados.', var_dump($users_found));
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        public function createUser($user_info_array) { 

            // verificando se veio tudo junto primeiro
            if(!array_has_keys(['username', 'nickname', 'email', 'password', 'birthdate'], $user_info_array)) {
                return generate_response(false, 400, 'Dados insuficientes para criar usuário', null, $user_info_array);
            }

            // validando o resto das coisas
            if(!$this->validateUsername($user_info_array['username'])) {
                return generate_response(false, 401, 'Nome de usuário só pode ter letras minúsculas, números e nenhum espaço, ao menos 4 caracteres.', null, $user_info_array['username']);
            }

            if(!$this->validatePassword($user_info_array['password'])) {
                return generate_response(false, 401, 'Senha precisa ter ao menos uma letra maiúscula, uma minúscula, um dígito e mínimo de 8 caracteres.', null, $user_info_array['password']);
            }

            if(!$this->validateEmail($user_info_array['email'])) {
                return generate_response(false, 401, 'E-mail precisa seguir formato padrão exemplo@gmail.com', null, $user_info_array['email']);
            }

            if(!$this->validateBirthdate($user_info_array['birthdate'])) {
                return generate_response(false, 401, 'Data de nascimento tem que estar em formato YYYY-MM-DD, não ser futura e estar depois de 1900-01-01.', null, $user_info_array['birthdate']);
            }

            // TRANSAÇÃO !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            try {

                // iniciando transação
                $this->conn->beginTransaction();

                // verifico se já existe usuário com esse user ou email🔥
                if($this->searchDuplicateUser($user_info_array['username'], $user_info_array['email'])) {
                    $this->conn->rollBack();
                    return generate_response(false, 409, 'Já existe um usuário com esse @ ou e-mail registrado.', null, ['username' => $user_info_array['username'], 'email' => $user_info_array['email']]);
                }
                
                // criando usuario
                $encrypted_password = password_hash($user_info_array['password'], PASSWORD_BCRYPT);

                $user_stmt = $this->conn->prepare('INSERT INTO users(username, email, password, nickname, birthdate) VALUES(:username, :email, :password, :nickname, :birthdate)');

                // executando registramento épico !!!!1!1! !! !  1!1!
                $new_user_info = [
                    'username' => $user_info_array['username'], 
                    'email' => $user_info_array['email'], 
                    'password' => $encrypted_password, 
                    'nickname' => $user_info_array['nickname'],
                    'birthdate' => $user_info_array['birthdate']
                ];
                $user_stmt->execute($new_user_info);

                // pegando id do usuario que foi registrado
                $last_saved_user = $this->conn->lastInsertId();

                // gerando nome aleatório de arquivo sem duplicar pra garrantir
                do {
                    $filename = bin2hex(random_bytes(16)) . '_savefile.json';
                } while(file_exists(UPLOAD_DIR . $filename));
                
                // criando save e pegando ID dele
                $save_stmt = $this->conn->prepare('INSERT INTO saves(savepath, userid) VALUES(:savepath, :userid)');
                $save_stmt->execute(['savepath' => $filename, 'userid' => $last_saved_user]);

                // salvando arquivo
                file_put_contents(UPLOAD_DIR . $filename, json_encode([]));
            
                $this->conn->commit();

                // voltando com uma resposta falano que deu certo

                // colocando id e tirando senha
                $new_user_info['id'] = $last_saved_user;
                unset($new_user_info['password']);
                return generate_response(true, 201, 'Sucesso no registro!', null, $new_user_info);

            } catch (Exception $err) {

                // fazendo rollback (caso esteja em transação) 
                $this->conn->rollBack();

                // retirando arquivo (caso exista)
                if(isset($filename) && is_file(UPLOAD_DIR . $filename)) {
                    @unlink(UPLOAD_DIR . $filename); // @ pra não dar warning
                }

                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage());

            }

        }

        public function updateUser($identifier, $info_array) {

            // validando identificador - se não for nem id nem user valido, eu retorno direto
            if(!$this->validateIdentifier($identifier)) {
                return generate_response(false, 400, 'Identificador inválido.', null, $identifier);
            }

            // validando cada campo fornecido e guardando no array a cada sucesso
            $to_be_altered = [];
            
            // username
            if(isset($info_array['username'])) {

                if($this->searchDuplicateUser($info_array['username'], null)) {
                    return generate_response(false, 409, 'Já existe um usuário com esse @', null, $info_array['username']);
                }
                    
                if(!$this->validateUsername($info_array['username'])) {
                    return generate_response(false, 401, 'Nome de usuário só pode ter letras minúsculas, números e nenhum espaço, ao menos 4 caracteres.', null, $info_array['username']);
                }

                $to_be_altered['username'] = $info_array['username'];

            }

            // nickname (não precisa de validação)
            if(isset($info_array['nickname'])) {

                $to_be_altered['nickname'] = $info_array['nickname'];

            }

            // email
            if(isset($info_array['email'])) {

                if($this->searchDuplicateUser(null, $info_array['email'])) {
                    return generate_response(false, 409, 'Já existe um usuário com esse email.', null, $info_array['email']);
                }
                    
                if(!$this->validateEmail($info_array['email'])) {
                    return generate_response(false, 401, 'E-mail precisa seguir formato padrão exemplo@gmail.com', null, $info_array['email']);
                }

                $to_be_altered['email'] = $info_array['email'];

            }

            // senha
            if(isset($info_array['password'])) {

                if(!$this->validatePassword($info_array['password'])) {
                    return generate_response(false, 401, 'Senha precisa ter ao menos uma letra maiúscula, uma minúscula, um dígito e mínimo de 8 caracteres.', null, $info_array['password']);
                }

                $to_be_altered['password'] = password_hash($info_array['password'], PASSWORD_BCRYPT);

            }

            // data de nascimento
            if(isset($info_array['birthdate'])) {

                if(!$this->validateBirthdate($info_array['birthdate'])) {
                    return generate_response(false, 409, 'Data de nascimento tem que estar em formato YYYY-MM-DD, não ser futura e estar depois de 1900-01-01.', null, $info_array['birthdate']);
                }

                $to_be_altered['birthdate'] = $info_array['birthdate'];

            }

            // tentando achar o usuário
            try {
                $query_first_part = 'SELECT id, username, nickname, email, password, birthdate FROM users WHERE ';

                if ($this->validateId($identifier)) {
                    $user_query = $this->conn->prepare($query_first_part . 'id = :id');
                    $user_query->execute(['id' => (int)$identifier]);
                } else {
                    $user_query = $this->conn->prepare($query_first_part . 'username = :username');
                    $user_query->execute(['username' => $identifier]);
                }

                $user_found = $user_query->fetch(PDO::FETCH_ASSOC);
                
                if(!$user_found) {
                    return generate_response(false, 404, 'Não existe usuário com esse identificador.', null, $identifier);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }

            // depois de achar o usuário, eu valido as novas infos criando um novo usuário e substituindo com os novos dados
            $new_user_info = $user_found;

            // se não tiver o que alterar, já retorno de uma vez
            if(empty($to_be_altered)) {
                return generate_response(true, 200, 'Nenhuma informação utilizável fornecida para alteração.', 'keys aceitas: username, nickname, email, password, birthdate', $info_array);
            }

            // criando código e parametros para a query update
            $query_fields = [];
            $query_params = ['id' => $user_found['id']];

            foreach($to_be_altered as $field => $value) {
                $query_fields[] = "$field = :$field";
                $query_params[$field] = $value;
                $new_user_info[$field] = $value;
            }


            // rodando query final!!!!!!
            try {
                
                $this->conn->beginTransaction();

                // juntando os fields pra colocar na query
                $update_set = implode(', ', $query_fields);

                $update_stmt = $this->conn->prepare("UPDATE users SET $update_set WHERE id = :id");
                $update_stmt->execute($query_params);

                $this->conn->commit();

                unset($new_user_info['password']);
                return generate_response(true, 200, 'Usuário atualizado com sucesso.', null, $new_user_info);


            } catch(PDOException $err) {

                $this->conn->rollBack();
                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage());

            }
        }

        public function deleteUser($identifier) {

            try {

                // deletando usuario com esse identificador
                $query_first_part = 'DELETE FROM users WHERE ';
                if ($this->validateId($identifier)) {
                    $delete_stmt = $this->conn->prepare($query_first_part . 'id = :id');
                    $delete_stmt->execute(['id' => (int)$identifier]);
                } else {
                    $delete_stmt = $this->conn->prepare($query_first_part . 'username = :username');
                    $delete_stmt->execute(['username' => $identifier]);
                }

                if($delete_stmt->rowCount() == 0) {
                    return generate_response(true, 404, 'Usuário não foi encontrado para remoção.', null);
                }
                
                return generate_response(true, 200, 'Usuário apagado com sucesso!', null);


            } catch(PDOException $err) {
                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }
    }

?>