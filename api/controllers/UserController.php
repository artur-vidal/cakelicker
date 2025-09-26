<?php

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
                echo json_encode(generate_response(false, 500, $err->getMessage()));
                exit;
            }
        }

        public function getLastUserId() {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users ORDER BY id DESC LIMIT 1');
                $id_query->execute();

                $val = $id_query->fetchAll(PDO::FETCH_COLUMN);

                return ($val) ? $val : null;

            } catch(PDOException $err) {
                echo json_encode(generate_response(false, 500, $err->getMessage()));
                exit;
            }
        }

        public function getUserById($id) {

            try {
                $stmt = $this->conn->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $id]);

                $user_found = $stmt->fetch(PDO::FETCH_ASSOC);

                if($user_found){
                    return generate_response(true, 200, 'usuário encontrado', $user_found);
                } else {
                    return generate_response(false, 404, 'não existe usuário com esse id', $id);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage(), $id);
            }
        }

        public function getUserByUsername($username) {

            try {
                $stmt = $this->conn->prepare('SELECT * FROM users WHERE username = :username');
                $stmt->execute(['username' => $username]);

                $user_found = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($user_found){
                    return generate_response(true, 200, 'usuário encontrado', $user_found[0]);
                } else {
                    return generate_response(false, 404, 'não existe usuário com esse username', $username);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage(), $username);
            }
        }

        public function getUserBySession($session_token) {

            try {
                $stmt = $this->conn->prepare('SELECT users.* FROM sessions JOIN users ON sessions.userid = users.id WHERE sessions.token = :token');
                $stmt->execute(['token' => $session_token]);

                $user_found = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($user_found){
                    return generate_response(true, 200, 'usuário encontrado', $user_found[0]);
                } else {
                    return generate_response(false, 404, 'essa sessão não existe');
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage());
            }
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
                $stmt = $this->conn->prepare("SELECT * FROM users ORDER BY $order_param $order_direction LIMIT :lim OFFSET :off");
                $stmt->bindValue(':off', $first_page, PDO::PARAM_INT);
                $stmt->bindValue(':lim', $per_page, PDO::PARAM_INT);
                $stmt->execute();

                $user_found = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($user_found){
                    return generate_response(true, 200, 'usuários encontrados', $user_found);
                } else {
                    return generate_response(false, 404, 'usuários não encontrados');
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage());
            }
        }

        public function createUser($username, $nickname, $email, $password, $birthdate) { 

            #region Validação

            if(!$this->validateUsername($username)) {
                return generate_response(false, 401, 'Nome de usuário só pode ter letras minúsculas, números e nenhum espaço, ao menos 4 caracteres.', $username);
            }

            if(!$this->validatePassword($password)) {
                return generate_response(false, 401, 'Senha precisa ter ao menos uma letra maiúscula, uma minúscula, um dígito e mínimo de 8 caracteres.', $password);
            }

            if(!$this->validateEmail($email)) {
                return generate_response(false, 401, 'E-mail precisa seguir formato padrão exemplo@gmail.com', $email);
            }

            if(!$this->validateBirthdate($birthdate)) {
                return generate_response(false, 401, 'Data de nascimento tem que estar em formato YYYY-MM-DD, não ser futura e estar depois de 1900-01-01.', $birthdate);
            }

            #endregion

            #region TRANSAÇão !!!!
            try {

                // iniciando transação
                $this->conn->beginTransaction();

                // verifico se já existe usuário com esse user ou email🔥

                if($this->searchDuplicateUser($username, $email)) {
                    $this->conn->rollBack();
                    return generate_response(false, 409, 'Já existe um usuário com esse @ ou e-mail registrado.');
                }

                // gerando nome aleatório de arquivo sem duplicar pra garrantir
                do {
                    $filename = bin2hex(random_bytes(16)) . '_savefile.json';
                } while(file_exists(UPLOAD_DIR . $filename));
                
                // criando save e pegando ID dele
                $save_stmt = $this->conn->prepare('INSERT INTO saves(savepath) VALUES(:savepath)');
                $save_stmt->execute(['savepath' => $filename]);
                $save_id = $this->conn->lastInsertId();

                // salvando arquivo
                file_put_contents(UPLOAD_DIR . $filename, json_encode([]));
                
                // crio, finalmente, um usuário usando esse id
                $encrypted_password = password_hash($password, PASSWORD_BCRYPT);

                $user_stmt = $this->conn->prepare('INSERT INTO users(username, email, password, nickname, saveid, birthdate) VALUES(:username, :email, :password, :nickname, :saveid, :birthdate)');

                // executando registramento épico !!!!1!1! !! !  1!1!
                $user_stmt->execute([
                    'username' => $username, 
                    'email' => $email, 
                    'password' => $encrypted_password, 
                    'nickname' => $nickname, 
                    'saveid' => $save_id, 
                    'birthdate' => $birthdate
                ]);
            
                $this->conn->commit();

            } catch (Exception $err) {

                // fazendo rollback (caso esteja em transação) 
                $this->conn->rollBack();

                // retirando arquivo (caso exista)
                if(isset($filename) && is_file(UPLOAD_DIR . $filename)) {
                    @unlink(UPLOAD_DIR . $filename); // @ pra não dar warning
                }

                return generate_response(false, 500, $err->getMessage());

            }

            #endregion

            // voltando com uma resposta falano qui deu serto
            return generate_response(true, 201, 'Sucesso no registro!');
        }

        public function updateUserPartial($identifier, $info_array) {

            // validando identificador - se não for nem id nem user valido, eu retorno direto
            if(!ctype_digit($identifier) && !$this->validateUsername($identifier)) {
                return generate_response(false, 400, 'Identificador inválido', $identifier);
            }

            // validando cada campo fornecido e guardando no array a cada sucesso
            $to_be_altered = [];
            
            // username
            if(isset($info_array['username'])) {

                if($this->searchDuplicateUser($info_array['username'], null)) {
                    return generate_response(false, 409, 'Já existe um usuário com esse @', $info_array['username']);
                }
                    
                if(!$this->validateUsername($info_array['username'])) {
                    return generate_response(false, 401, 'Nome de usuário só pode ter letras minúsculas, números e nenhum espaço, ao menos 4 caracteres.', $info_array['username']);
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
                    return generate_response(false, 409, 'Já existe um usuário com esse email', $info_array['email']);
                }
                    
                if(!$this->validateEmail($info_array['email'])) {
                    return generate_response(false, 401, 'E-mail precisa seguir formato padrão exemplo@gmail.com', $info_array['email']);
                }

                $to_be_altered['email'] = $info_array['email'];

            }

            // senha
            if(isset($info_array['password'])) {

                if(!$this->validatePassword($info_array['password'])) {
                    return generate_response(false, 401, 'Senha precisa ter ao menos uma letra maiúscula, uma minúscula, um dígito e mínimo de 8 caracteres.', $info_array['password']);
                }

                $to_be_altered['password'] = password_hash($info_array['password'], PASSWORD_BCRYPT);

            }

            // data de nascimento
            if(isset($info_array['birthdate'])) {

                if(!$this->validateBirthdate($info_array['birthdate'])) {
                    return generate_response(false, 409, 'Data de nascimento tem que estar em formato YYYY-MM-DD, não ser futura e estar depois de 1900-01-01.', $info_array['birthdate']);
                }

                $to_be_altered['birthdate'] = $info_array['birthdate'];

            }

            // tentando achar o usuário
            try {
                $user_query = $this->conn->prepare('SELECT id, username, nickname, email, password, birthdate FROM users WHERE id = :identifier OR username = :identifier');
                $user_query->execute(['identifier' => $identifier]);

                $user_found = $user_query->fetch(PDO::FETCH_ASSOC);
                
                if(!$user_found) {
                    return generate_response(false, 404, 'Não existe usuário com esse user ou e-mail', $identifier);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage());
            }

            // depois de achar o usuário, eu valido as novas infos criando um novo usuário e substituindo com os novos dados
            $new_user_info = $user_found;

            // se não tiver o que alterar, já retorno de uma vez
            if(empty($to_be_altered)) {
                return generate_response(true, 200, 'Nenhuma informação utilizável fornecida para alteração', $info_array);
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

                // tirando a senha hasheada da resposta porque é L e os hackers vão pocar 🔥🔥
                unset($new_user_info['password']);

                return generate_response(true, 200, 'Usuário atualizado com sucesso', $new_user_info);


            } catch(PDOException $err) {

                $this->conn->rollBack();
                return generate_response(false, 500, $err->getMessage());

            }
        }

        public function deleteUser() {

        }
    }

?>