<?php

    namespace Cakelicker\Controllers;
    use Cakelicker\Traits\ValidationTraits;
    use Cakelicker\Models\{UserModel, SaveModel};
    use Cakelicker\Helpers\ResponseHelper;

    class SaveController {

        use ValidationTraits;

        private $conn;
        private $saveModel;
        private $userModel;

        public function __construct($dbconn) {

            $this->saveModel = new SaveModel($dbconn);
            $this->userModel = new UserModel($dbconn);

        }

        public function getSave($identifier, $save_identifier) {

            $save_identifier = max(1, $save_identifier);
            
            if($save_identifier > SAVES_PER_USER)
                return ResponseHelper::generateBuilder(false, 400, 'Cada usuário só pode ter no máximo 3 saves.', null, $save_identifier);

            try {
                
                if(!is_numeric($identifier))
                    $identifier = $this->userModel->findNumericIdFromUsername($identifier);

                if(!$identifier)
                    return ResponseHelper::generateBuilder(false, 404, 'Não existe usuário com esse identificador, ou o identificador é inválido.', null);
                
                $saves_found = $this->saveModel->getSavesByUserId($identifier);

                if($saves_found) {
                    if(isset($saves_found[$save_identifier - 1]))
                        return ResponseHelper::generateBuilder(true, 200, 'Save encontrado.', null, $saves_found[$save_identifier - 1]);
                    else
                        return ResponseHelper::generateBuilder(false, 404, 'O usuário não tem esse save.', null, $save_identifier);
                } else {
                    return ResponseHelper::generateBuilder(false, 404, 'Não foi encontrado nenhum save vinculado a esse usuário. Certifique-se de que o usuário em questão existe.', null);
                }

            } catch(PDOException $err) {
                return ResponseHelper::generateBuilder(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        public function getSaves($identifier) {

            try {
                
                if(!is_numeric($identifier))
                    $identifier = $this->userModel->findNumericIdFromUsername($identifier);

                if(!$identifier)
                    return ResponseHelper::generateBuilder(false, 404, 'Não existe usuário com esse identificador, ou o identificador é inválido.', null);
                
                $saves_found = $this->saveModel->getSavesByUserId($identifier);

                if($saves_found) {
                    return ResponseHelper::generateBuilder(true, 200, 'Saves encontrados.', null, $saves_found);
                } else {
                    return ResponseHelper::generateBuilder(false, 404, 'Não foi encontrado nenhum save vinculado a esse usuário. Certifique-se de que o usuário em questão existe.', null, $identifier);
                }

            } catch(PDOException $err) {
                return ResponseHelper::generateBuilder(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }
    }

?>