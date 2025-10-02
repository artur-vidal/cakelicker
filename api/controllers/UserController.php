<?php

    namespace Cakelicker\Controllers;
    use Cakelicker\Traits\ValidationTraits;
    use Cakelicker\Models\UserModel;
    use Cakelicker\Helpers\{ResponseHelper, ArrayHelper};
    use Cakelicker\ValueObjects\{PaginationParams, UserBuilder};

    use PDO;
    use PDOException;
    use Exception;

    class UserController {
        
        private $userModel;

        public function __construct($dbconn) {

            // definindo variáveis
            $this->userModel = new UserModel($dbconn);

            // garantindo que a pasta de saves exista
            if(!is_dir(UPLOAD_DIR)){
                mkdir(UPLOAD_DIR);
            }
        }

        public function getUser($identifier) {
            try {
                if($this->isPreset($identifier))
                    $identifier = $this->presetToId($identifier);

                $user_found = $this->userModel->getUser($identifier);

                if($user_found){
                    return ResponseHelper::generate(true, 200, 'Usuário encontrado.', null, $user_found);
                } else {
                    return ResponseHelper::generate(false, 404, 'Não existe usuário com esse identificador.', null, $identifier);
                }
            } catch(PDOException $err) {
                return ResponseHelper::generate(false, 500, 'Erro no banco de dados.', $err->getMessage(), $identifier);
            }
        }

        public function getPagedUsers($page, $offset, $per_page, $order_param, $order_direction) {
            $paginationParamsObject = new PaginationParams($page,  $per_page, $offset, $order_param, $order_direction);

            try {
                $users_found = $this->userModel->getPagedUsers($paginationParamsObject);

                if($users_found){
                    return ResponseHelper::generate(true, 200, 'Usuários encontrados.', null, $users_found);
                } else {
                    return ResponseHelper::generate(false, 404, 'Usuários não foram encontrados.', null);
                }
            } catch(PDOException $err) {
                return ResponseHelper::generate(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        public function createUser($user_info_array) { 
            $user_columns = ['username', 'nickname', 'email', 'password', 'birthdate'];
            if(!ArrayHelper::arrayHasKeys($user_columns, $user_info_array))
                return ResponseHelper::generate(false, 400, 'Dados insuficientes para criar usuário', null, $user_info_array);

            try {
                $user_builder = new UserBuilder();
                $user_builder->withUsername($user_info_array['username'])
                    ->withNickname($user_info_array['nickname'])
                    ->withEmail($user_info_array['email'])
                    ->withPassword($user_info_array['password'])
                    ->withBirthdate($user_info_array['birthdate']);

                if(!$user_builder->isComplete()) {
                    return ResponseHelper::generate(false, 400, 'Dados insuficientes para criação do usuário.', null, $user_info_array);
                }

                $user_object = $user_builder->build();

                $created_user_id = $this->userModel->createUserAndGetId($user_object);

                $response_data = ArrayHelper::filterArrayKeys($user_columns, $user_info_array);
                $response_data['id'] = $created_user_id;
                unset($response_data['password']);
                return ResponseHelper::generate(true, 201, 'Sucesso no registro!', null, $response_data);
            } catch (PDOException $err) {
                return ResponseHelper::generate(false, 500, 'Erro no banco de dados.', $err->getMessage());
            } catch (Exception $err) {
                return ResponseHelper::generate(false, $err->getCode(), null, $err->getMessage());
            }
        }

        public function updateUser($identifier, $info_array) {
            try {
                $user_builder = new UserBuilder();
                $user_object = $user_builder->fillFromAssocArrayAndBuild($info_array);

                $updated_user = $this->userModel->updateUserAndReturn($identifier, $user_object);

                return ResponseHelper::generate(true, 200, 'Usuário atualizado com sucesso.', null, $updated_user);
            } catch(Exception $err) {
                return ResponseHelper::generate(false, $err->getCode(), null, $err->getMessage());
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
                    return ResponseHelper::generate(false, 404, 'Usuário não foi encontrado para remoção.', null, $identifier);
                }
                
                return ResponseHelper::generate(true, 200, 'Usuário apagado com sucesso!', null);


            } catch(PDOException $err) {
                return ResponseHelper::generate(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        private function isPreset($value) {
            return in_array($value, USER_ENDPOINT_PRESETS);
        }

        private function presetToId($preset) {
            try {
                switch($preset) {
                    case 'first':
                        $new_id = $this->userModel->getFirstUserId();
                        break;

                    case 'last':
                        $new_id = $this->userModel->getLastUserId();
                        break;
                }

                return $new_id;
            } catch(Exception $err) {
                $error_response = ResponseHelper::generate(false, 500, 'Erro no banco de dados.', $err->getMessage());
                ResponseHelper::respond($error_response);
            }
        }

    }

?>