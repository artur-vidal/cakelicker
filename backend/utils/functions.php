<?php
    define("SESSION_COOKIE_NAME", 'cakelicker_session');
    define("IS_LOCAL", $_SERVER['SERVER_NAME'] === 'localhost');

    function create_session($db_connection, $user_id, $days_to_expire) {

        // criando token e data de expiração
        $token = bin2hex(random_bytes(32));
        $expires_at = time() + 86400 * $days_to_expire;
        $formatted_expiring_date = date('Y-m-d H:i:s', $expires_at);

        // guardando no banco de dados e, se der tudo certo, guardo nos cookies
        try {
            $stmt = $db_connection->prepare('INSERT INTO sessions(token, idusuario, expiresat) VALUES(:token, :idusuario, :expiresat)');
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':idusuario', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':expiresat', $formatted_expiring_date, PDO::PARAM_STR);
            $stmt->execute();
            
            setcookie(SESSION_COOKIE_NAME, $token, [
                'expires' => time() + 84600 * $days_to_expire,
                'httponly' => true,
                'secure' => !IS_LOCAL
            ]);

        } catch(PDOException $err) {

            echo 'Erro ao criar sessão: ' . $err;
            exit;
        }

    }

    function remove_session($db_connection, $token){

        // removendo do banco e depois do navegador
        try {
            $stmt = $db_connection->prepare('DELETE FROM sessions WHERE token = :token');
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();

            setcookie(SESSION_COOKIE_NAME, '', time() - 3600);
        } catch(PDOException $err) {
            // echo 'Erro ao remover sessão: ' . $err->getMessage();
            exit;
        }
    }

    function get_user_name($db_connection, $id) {

        // procurando essa sessão no banco e retorno ID se achar
        try {
            $query = $db_connection->prepare('SELECT nickname FROM usuarios WHERE id = :id');
            $query->bindParam(':id', $id, PDO::PARAM_INT);
            $query->execute();

            $found_data = $query->fetchAll(PDO::FETCH_ASSOC);

            if($found_data) {
                return $found_data[0]['nickname'];
            } else {
                echo 'Usuário não encontrado';
            }

        } catch(PDOException $err) {
            echo 'Erro ao procurar nickname: ' . $err->getMessage();
            exit;
        }
    }

    function generate_response($success, $code, $message, $data = null) {
        $debug_info = debug_backtrace();
        
        $resposta = [
            'caller_origin' => $debug_info[0]['file'],
            'line_called' => $debug_info[0]['line'],
            'status' => ($success) ? 'success' : 'failure',
            'code' => $code,
            'message' => $message,
            'data' => $data
        ];

        return json_encode($resposta);
    }

    function debug_interrupt($data = null) {
        echo json_encode(['debug', $data]);
        exit;
    }

?>