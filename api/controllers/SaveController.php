<?php

    namespace Cakelicker\Controllers;
    use Cakelicker\Traits\ValidationTraits;
    use Cakelicker\Models\{UserModel, SaveModel};
    use Cakelicker\Helpers\ResponseHelper;
    use Cakelicker\ValueObjects\SaveBuilder;

    class SaveController {

        private $conn;
        private $saveModel;
        private $userModel;

        public function __construct($dbconn) {

            $this->saveModel = new SaveModel($dbconn);
            $this->userModel = new UserModel($dbconn);

        }

        public function getSavesOfUser($user_identifier) {
            if($user_identifier === null)
                return ResponseHelper::generateBuilder(false, 404, 'Identificador de usuário inválido.', null);
            
            try {
                if(!is_numeric($user_identifier))
                    $user_identifier = $this->userModel->usernameToId($user_identifier);

                $saves_found = $this->saveModel->getSavesByUserId($user_identifier);

                if($saves_found) {
                    return ResponseHelper::generateBuilder(true, 200, 'Saves encontrados.', null, $saves_found);
                } else {
                    return ResponseHelper::generateBuilder(false, 404, 'Não foi encontrado nenhum save vinculado a esse usuário. Certifique-se de que o usuário em questão existe.', null, $user_identifier);
                }
            } catch(\PDOException $err) {
                return ResponseHelper::generateBuilder(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        public function createSave($user_identifier, $save_info = null) {
            if($user_identifier === null) 
                return ResponseHelper::generateBuilder(false, 400, 'Usuário não informado.');

            try {

                if($save_info === null) {
                    $created_save_id = $this->saveModel->createSaveAndGetId($user_identifier);
                } else {
                    $save_builder = new SaveBuilder();
                    $save_builder->fillFromAssocArray($save_info);

                    if(!$save_builder->isEmpty() && !$save_builder->isComplete())
                        return ResponseHelper::generateBuilder(false, 400, 'Criação de save aceita apenas corpo de requisição vazio ou completo.', null, $save_info);

                    $save_object = $save_builder->build();
                    $created_save_id = $this->saveModel->createSaveAndGetId($user_identifier, $save_object);
                }

                $save_data_for_response = [
                    'id' => $created_save_id,
                    'userid' => $user_identifier
                ];

                return ResponseHelper::generateBuilder(true, 200, 'Save criado com sucesso!', null, $save_data_for_response);

            } catch(\Exception $err) {
                return ResponseHelper::generateBuilder(false, $err->getCode(), null, $err->getMessage());

            }
        }
    }

?>