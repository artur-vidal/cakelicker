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

        public function getUserById($id) {

            try {
                $stmt = $this->conn->prepare('SELECT * FROM users WHERE id = :id');
                $stmt->execute(['id' => $id]);

                $user_found = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($user_found){
                    return generate_response(true, 200, 'usuário encontrado', $user_found[0]);
                } else {
                    return generate_response(false, 404, 'não existe usuário com esse id', $id);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage(), $value);
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
                $stmt = $this->conn->prepare('SELECT users.* FROM sessions JOIN sessions users ON sessions.userid = users.id WHERE sessions.token = :token');
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

            if($page < 1) {
                return generate_response(false, 400, 'getUsers() não aceita páginas abaixo de 1');
            }

            // restringindo parâmetros permitidos por segurança
            $column_whitelist = ['id', 'username', 'email', 'nickname', 'birthdate', 'creationdate'];
            $order_direction_whitelist = ['ASC', 'DESC'];

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

        public function getFirstUserId() {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users ORDER BY id ASC LIMIT 1');
                $id_query->execute();

                $val = $id_query->fetch(PDO::FETCH_COLUMN);

                return ($val) ? $val : null;

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage(), $value);
            }
        }

        public function getLastUserId() {
            try {
                $id_query = $this->conn->prepare('SELECT id FROM users ORDER BY id DESC');
                $id_query->execute();

                $val = $id_query->fetchAll(PDO::FETCH_ASSOC);

                return ($val) ? $val[0]['id'] : null;

            } catch(PDOException $err) {
                return generate_response(false, 500, $err->getMessage(), $value);
            }
        }

        public function createUser($username, $nickname, $email, $password, $birthdate) { 

            #region Validação

            $username_expression = '/^[a-z0-9_]{4,20}$/'; // deve ter até 20 caracteres minusculos, numeros, sem espaço, e apenas _ como caractere especial
            $password_expression = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/'; // pelo menos 8 caracteres e com número
            $email_expression = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'; // exemplo@email.com
            $birthdate_expression = '/^\d{4}-\d{2}-\d{2}$/'; // formato YYYY-MM-DD

            // user
            if(!preg_match($username_expression, $username)) return generate_response(false, 401, 'Nome de usuário só pode ter letras minúsculas, números e nenhum espaço, ao menos 4 caracteres.', $username);
 
            // senha
            if(!preg_match($password_expression, $password)) return generate_response(false, 401, 'Senha precisa ter ao menos uma letra maiúscula, uma minúscula, um dígito e mínimo de 8 caracteres.', $password);

            // email
            if(!preg_match($email_expression, $email)) return generate_response(false, 401, 'E-mail precisa seguir formato padrão.', $email);

            // data de nascimento 
            if(!preg_match($birthdate_expression, $birthdate)) return generate_response(false, 401, 'Data de nascimento tem que estar em formato YYYY-MM-DD.', $birthdate);

            // depois que eu verifiquei o formato da data, preciso ver se ela não é futura
            if(strtotime($birthdate) > time()) return generate_response(false, 401, 'Data de nascimento futura.', $birthdate);

            #endregion

            #region TRANSAÇão !!!!
            try {

                // iniciando transação
                $this->conn->beginTransaction();

                // verifico se já existe usuário com esse user ou email🔥
                $same_user_query = $this->conn->prepare('SELECT username FROM users WHERE username = :username OR email = :email');
                $same_user_query->execute(['username' => $username, 'email' => $email]);
                $same_user = $same_user_query->fetch(PDO::FETCH_ASSOC);

                if($same_user) {
                    $this->conn->rollBack();
                    return generate_response(false, 409, 'Já existe um usuário com esse @ ou e-mail registrado.');
                }

                // gerando nome aleatório de arquivo sem duplicar pra garrantir
                do {
                    $filename = bin2hex(random_bytes(16)) . '_savefile.json';
                } while(file_exists(UPLOAD_DIR . $filename));
                
                // criando save e pegando ID dele
                $save_stmt = $this->conn->prepare('INSERT INTO saves(name, cakes, xp, level, prestige, rebirths, savepath) VALUES("Desconhecido", "0", 0, 1, 0, 0, :savepath)');
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

            } catch (PDOException $err) {

                // fazendo rollback (caso esteja em transação) 
                if($this->conn->inTransaction()) {
                    $this->conn->rollBack();
                }

                // retirando arquivo (caso exista)
                if(isset($filename) and file_exists(UPLOAD_DIR . $filename)) {
                    unlink(UPLOAD_DIR . $filename);
                }

                return generate_response(false, 500, $err->getMessage());
            }

            #endregion

            // voltando com uma resposta falano qui deu serto
            return generate_response(true, 201, 'Sucesso no registro!');
        }

        public function deleteUser() {

        }

        public function updateUser() {

        }
    }

?>