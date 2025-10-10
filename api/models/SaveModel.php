<?php

    namespace Cakelicker\Models;
    use Cakelicker\Traits\ValidationTraits;
    
    class SaveModel {

        use ValidationTraits;

        private $conn;
        private $saveDir;

        private $querySelectColumns;
        private $querySelectColumnsAsString;
        
        public function __construct($dbconn) {
            $this->conn = $dbconn;
            $this->saveDir = UPLOAD_DIR . 'saves\\';

            $this->querySelectColumns = ['id', 'name', 'cakes_coefficient', 'cakes_exponent', 'xp', 'level', 'prestige', 'rebirths', 'savepath', 'userid'];
            $this->querySelectColumnsAsString = implode(', ', $this->querySelectColumns);
        }

        public function getSaveById($save_id) {
            try {
                $save_query = $this->conn->prepare("SELECT $this->querySelectColumnsAsString FROM saves WHERE id = :id");
                $save_query->execute(['id' => $save_id]);

                $saves_found = $save_query->fetch(\PDO::FETCH_ASSOC);

                return $saves_found;
            } catch(\PDOException $err) {
                throw $err; // o controller resolve essa parada aqui
            }
        }

        public function getSavesByUserId($user_id) {
            try {
                $save_query = $this->conn->prepare("SELECT $this->querySelectColumnsAsString FROM saves WHERE userid = :userid");
                $save_query->execute(['userid' => $user_id]);

                $saves_found = $save_query->fetchAll(\PDO::FETCH_ASSOC);
                
                return $saves_found;
            } catch(\PDOException $err) {
                throw $err;
            }
        }

        public function createSaveAndGetId($user_id, $user_object = null) {

            if(!$this->validateId($user_id))
                throw new \Exception('ID inválido.', 400);

            if($this->userReachedSaveLimit($user_id))
                throw new \Exception('O usuário já atingiu o limite de ' . SAVES_PER_USER . ' saves por usuário.', 409);

            try {

                $this->conn->beginTransaction();
                
                $filename = $this->generateFilename();

                if($user_object === null) {
                    $params = [];
                    $query = 'INSERT INTO saves(savepath, userid) VALUES(:savepath, :userid)';
                } else {
                    $params = $user_object->toAssocArray();
                    $query = 'INSERT INTO saves(name, cakes_coefficient, cakes_exponent, xp, level, prestige, rebirths, savepath, userid) VALUES(:name, :cakes_coefficient, :cakes_exponent, :xp, :level, :prestige, :rebirths, :savepath, :userid)';
                }
                
                $params['userid'] = (int)$user_id;
                $params['savepath'] = $filename;

                $save_statement = $this->conn->prepare($query);
                $save_statement->execute($params);

                $this->createSaveFile($filename);

                $created_save_id = $this->conn->lastInsertId();

                $this->conn->commit();

                return $created_save_id;

            } catch(\Exception $err) {
                if($this->conn->inTransaction())
                    $this->conn->rollBack();

                if(isset($filename) && is_file($this->saveDir . $filename))
                    unlink($this->saveDir . $filename);

                throw $err;
            }
        }

        private function createSaveFile($filename) {
            file_put_contents($this->saveDir . $filename, '{}');
        }

        private function generateFilename() {
            do {
                $filename = bin2hex(random_bytes(8)) . '.json';
            } while(is_file($this->saveDir . $filename));
            return $filename;
        }

        private function userReachedSaveLimit($user_id) {
            try {
                $save_count_query = $this->conn->prepare('SELECT COUNT(*) FROM saves WHERE userid = :userid');
                $save_count_query->execute(['userid' => $user_id]);

                $save_count = $save_count_query->fetch(\PDO::FETCH_COLUMN);
                return $save_count >= SAVES_PER_USER;
            } catch(\Exception $err) {
                throw $err;
            }
        }

        public function removeUselessSavesAndGetCount() {
            $total_removed = 0;
            $files = scandir($this->saveDir);

            try {
                $save_query = $this->conn->prepare('SELECT savepath FROM saves');
                $save_query->execute();

                $savelist = $save_query->fetchAll(\PDO::FETCH_COLUMN);
                
                for($i = 0; $i < count($files); $i++) {
                    $cur_element = $this->saveDir . $files[$i];

                    $not_a_file = !is_file($cur_element);
                    if($not_a_file || ($cur_element === '.' || $cur_element === '..')) {
                        continue;
                    }

                    if(!in_array($cur_element, $savelist)) {
                        unlink($cur_element);
                        $total_removed++;
                    }
                }
            } catch(\Exception $err) {
                throw new \Exception('Erro no cleanup de saves.', 500);
            }

            return $total_removed;
        }

    }


?>