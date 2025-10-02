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
                return ResponseHelper::generate(false, 400, 'Cada usuário só pode ter no máximo 3 saves.', null, $save_identifier);

            try {
                
                $identifier = $this->userModel->getNumericIdFromIdentifier($identifier);

                if(!$identifier)
                    return ResponseHelper::generate(false, 404, 'Não existe usuário com esse identificador, ou o identificador é inválido.', null);
                
                $saves_found = $this->saveModel->getSavesByUserId($identifier);

                if($saves_found) {
                    if(isset($saves_found[$save_identifier - 1]))
                        return ResponseHelper::generate(true, 200, 'Save encontrado.', null, $saves_found[$save_identifier - 1]);
                    else
                        return ResponseHelper::generate(false, 404, 'O usuário não tem esse save.', null, $save_identifier);
                } else {
                    return ResponseHelper::generate(false, 404, 'Não foi encontrado nenhum save vinculado a esse usuário. Certifique-se de que o usuário em questão existe.', null);
                }

            } catch(PDOException $err) {
                return ResponseHelper::generate(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        public function getSaves($identifier) {

            try {
                
                $identifier = $this->userModel->getNumericIdFromIdentifier($identifier);

                if(!$identifier)
                    return ResponseHelper::generate(false, 404, 'Não existe usuário com esse identificador, ou o identificador é inválido.', null);
                
                $saves_found = $this->saveModel->getSavesByUserId($identifier);

                if($saves_found) {
                    return ResponseHelper::generate(true, 200, 'Saves encontrados.', null, $saves_found);
                } else {
                    return ResponseHelper::generate(false, 404, 'Não foi encontrado nenhum save vinculado a esse usuário. Certifique-se de que o usuário em questão existe.', null, $identifier);
                }

            } catch(PDOException $err) {
                return ResponseHelper::generate(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }
    }

?>