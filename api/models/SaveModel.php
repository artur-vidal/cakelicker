<?php

    namespace Cakelicker\Models;

    use PDO;
    use PDOException;
    
    class SaveModel {
        private $conn;
        
        public function __construct($dbconn) {
            $this->conn = $dbconn;
        }

        public function removeUselessSaves() {
            // reunindo saves inutilizados
            $total_removed = 0;
            try {
                $save_query = $this->conn->prepare('SELECT savepath FROM saves');
                $save_query->execute();

                $savelist = $save_query->fetchAll(PDO::FETCH_COLUMN);
                
                $dir_list = scandir(UPLOAD_DIR);
                
                for($i = 0; $i < count($dir_list); $i++) {
                    $cur_element = $dir_list[$i];

                    if(!is_file(UPLOAD_DIR . $cur_element) or $cur_element === '.' or $cur_element === '..') {
                        continue;
                    }

                    if(!in_array($cur_element, $savelist)) {
                        unlink(UPLOAD_DIR . $cur_element);
                        $total_removed++;
                    }
                }

            } catch(Exception $err) {
                // ignorando caso não dê certo
            }

            return $total_removed;
        }

        public function getSaveById($save_id) {

            try {
                $save_query = $this->conn->prepare('SELECT id, name, cakes_coefficient, cakes_exponent, xp, level, prestige, rebirths, userid, creationdate FROM saves WHERE id = :id');
                $save_query->execute(['id' => $save_id]);

                $saves_found = $save_query->fetch(PDO::FETCH_ASSOC);

                return $saves_found;
            } catch(PDOException $err) {
                throw $err; // o controller resolve essa parada aqui
            }
        }

        public function getSavesByUserId($identifier) {

            try {
                $save_query = $this->conn->prepare('SELECT id, name, cakes_coefficient, cakes_exponent, xp, level, prestige, rebirths, userid, creationdate FROM saves WHERE userid = :userid');
                $save_query->execute(['userid' => $identifier]);

                $saves_found = $save_query->fetchAll(PDO::FETCH_ASSOC);
                
                return $saves_found;
            } catch(PDOException $err) {
                throw $err;
            }
        }
    }


?>