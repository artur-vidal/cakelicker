<?php

    namespace Cakelicker\Controllers;
    use Cakelicker\Traits\ValidationTraits;
    use Cakelicker\Models\UserModel;
    use Cakelicker\Helpers\{ResponseHelper, ArrayHelper};
    use Cakelicker\ValueObjects\{PaginationParams, UserBuilder};

    class UserController {
        
        private $userModel;
        private $userColumns;

        public function __construct($dbconn) {
            $this->userModel = new UserModel($dbconn);
            $this->userColumns = ['username', 'nickname', 'email', 'password', 'birthdate'];
        }

        public function getUser($identifier) {
            try {
                if($this->isPreset($identifier))
                    $identifier = $this->presetToId($identifier);

                $user_found = $this->userModel->getUser($identifier);

                if($user_found){
                    return ResponseHelper::generateBuilder(true, 200, 'Usuário encontrado.', null, $user_found);
                } else {
                    return ResponseHelper::generateBuilder(false, 404, 'Não existe usuário com esse identificador.', null, $identifier);
                }
            } catch(\PDOException $err) {
                return ResponseHelper::generateBuilder(false, 500, 'Erro no banco de dados.', $err->getMessage(), $identifier);
            }
        }

        public function getPagedUsers($page, $offset, $per_page, $order_param, $order_direction) {
            $paginationParamsObject = new PaginationParams($page,  $per_page, $offset, $order_param, $order_direction);

            try {
                $users_found = $this->userModel->getPagedUsers($paginationParamsObject);

                if($users_found){
                    return ResponseHelper::generateBuilder(true, 200, 'Usuários encontrados.', null, $users_found);
                } else {
                    return ResponseHelper::generateBuilder(false, 404, 'Usuários não foram encontrados.', null);
                }
            } catch(\PDOException $err) {
                return ResponseHelper::generateBuilder(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        public function createUser($user_info) {
            try {
                $user_builder = new UserBuilder();
                $user_builder->fillFromAssocArray($user_info);

                if(!$user_builder->isComplete())
                    return ResponseHelper::generateBuilder(false, 400, 'Dados insuficientes para criação do usuário.', null, $user_info);

                $user_object = $user_builder->build();

                $created_user_id = $this->userModel->createUserAndGetId($user_object);

                $user_data_for_response = $user_object->toAssocArray();
                $user_data_for_response['id'] = $created_user_id;
                unset($user_data_for_response['password']);
                
                return ResponseHelper::generateBuilder(true, 201, 'Sucesso no registro!', null, $user_data_for_response);

            } catch (\PDOException $err) {
                return ResponseHelper::generateBuilder(false, 500, 'Erro no banco de dados.', $err->getMessage());

            } catch (\Exception $err) {
                return ResponseHelper::generateBuilder(false, $err->getCode(), null, $err->getMessage());
            }
        }

        public function updateUser($identifier, $info_array) {
            try {
                $user_builder = new UserBuilder();
                $user_object = $user_builder->fillFromAssocArrayAndBuild($info_array);

                $updated_user = $this->userModel->updateUserAndReturn($identifier, $user_object);

                return ResponseHelper::generateBuilder(true, 200, 'Usuário atualizado com sucesso.', null, $updated_user);
            } catch(\Exception $err) {
                return ResponseHelper::generateBuilder(false, $err->getCode(), null, $err->getMessage());
            }
        }

        public function deleteUser($identifier) {
            try {
                $this->userModel->deleteUser($identifier);
                return ResponseHelper::generateBuilder(true, 200, 'Usuário removido com sucesso.', null);
            } catch(\Exception $err) {
                return ResponseHelper::generateBuilder(false, $err->getCode(), null, $err->getMessage());
            }
        }

        public function isPreset($value) {
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
            } catch(\Exception $err) {
                $error_response_builder = ResponseHelper::generateBuilder(false, 500, 'Erro no banco de dados.', $err->getMessage());
                ResponseHelper::buildAndRespond($error_response_builder);
            }
        }

    }

?>