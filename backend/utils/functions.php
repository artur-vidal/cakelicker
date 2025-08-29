<?php
    const SESSION_COOKIE_NAME = 'cakelicker_session';

    function create_session($db_connection, $user_id, $days_to_expire) {

        // criando token e data de expiração
        $token = bin2hex(random_bytes(16));
        $expires_at = time() + 86400 * $days_to_expire;
        $formatted_expiring_date = date('Y-m-d', $expires_at);

        // guardando no banco de dados e, se der tudo certo, guardo nos cookies
        try {
            $stmt = $db_connection->prepare('INSERT INTO sessions(token, idusuario, expiresat) VALUES(:token, :idusuario, :expiresat)');
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':idusuario', $user_id);
            $stmt->bindParam(':expiresat', $formatted_expiring_date);
            $stmt->execute();
            
            setcookie(SESSION_COOKIE_NAME, $token);

            return generate_response(true, 'OK', 'Sessão criada com sucesso.');
            exit();

        } catch(PDOException $err) {
            return generate_response(false, 'PDO_ERROR', $err->getMessage());
            exit();
        }

    }

    function remove_session($db_connection, $token){

        // removendo do banco e depois do navegador
        try {
            $stmt = $db_connection->prepare('DELETE FROM sessions WHERE token = :token');
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            setcookie(SESSION_COOKIE_NAME, '', time() - 3600);
        } catch(PDOException $err) {
            echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        }
    }

    function generate_response($success, $code, $message, $data = null) {
        $debug_info = debug_backtrace();
        
        $resposta = [
            'caller_origin' => $debug_info[0]['file'],
            'line_called' => $debug_info[0]['line'],
            'error_code' => $code,
            'message' => $message,
            'data' => $data
        ];

        return json_encode($resposta);
    }

    function debug_interrupt($data = null) {
        echo json_encode(['debug', $data]);
        exit();
    }
?>