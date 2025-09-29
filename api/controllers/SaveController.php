<?php

    require_once __DIR__ . '\\traits\\ValidationTraits.php';

    class SaveController {

        use ValidationTraits;

        private $conn;

        public function __construct($dbconn) {

            $this->conn = $dbconn;

        }

        public function getSave($identifier, $save_identifier) {

            $save_identifier = max(1, $save_identifier);
            
            if($save_identifier > SAVES_PER_USER)
                return generate_response(false, 400, 'Cada usuário só pode ter no máximo 3 saves.', null, $save_identifier);

            try {
                
                $identifier = $this->getNumericIdFromIdentifier($identifier);
                
                $save_query = $this->conn->prepare('SELECT id, name, cakes_coefficient, cakes_exponent, xp, level, prestige, rebirths, userid, creationdate FROM saves WHERE userid = :userid');
                $save_query->execute(['userid' => $identifier]);

                $saves_found = $save_query->fetchAll(PDO::FETCH_ASSOC);

                if($saves_found) {
                    if(isset($saves_found[$save_identifier - 1]))
                        return generate_response(true, 200, 'Save encontrado.', null, $saves_found[$save_identifier - 1]);
                    else
                        return generate_response(false, 404, 'O usuário não tem esse save.', null, $save_identifier);
                } else {
                    return generate_response(false, 404, 'Não foi encontrado nenhum save vinculado a esse usuário. Certifique-se de que o usuário em questão existe.', null);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }

        public function getSaves($identifier) {

            try {
                
                $identifier = $this->getNumericIdFromIdentifier($identifier);
                
                $save_query = $this->conn->prepare('SELECT id, name, cakes_coefficient, cakes_exponent, xp, level, prestige, rebirths, userid, creationdate FROM saves WHERE userid = :userid');
                $save_query->execute(['userid' => $identifier]);

                $saves_found = $save_query->fetchAll(PDO::FETCH_ASSOC);

                if($saves_found) {
                    return generate_response(true, 200, 'Saves encontrados.', null, $saves_found);
                } else {
                    return generate_response(false, 404, 'Não foi encontrado nenhum save vinculado a esse usuário. Certifique-se de que o usuário em questão existe.', null, $identifier);
                }

            } catch(PDOException $err) {
                return generate_response(false, 500, 'Erro no banco de dados.', $err->getMessage());
            }
        }
    }

?>