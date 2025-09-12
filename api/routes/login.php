<?php
    require_once __DIR__ . "/../config/db.php";
    require_once __DIR__ . "/../utils/functions.php";

    $data = json_decode(file_get_contents("php://input"), true);

    // fazeno as verificação se houverem dados
    if($data) {
        
        $user = $data['user'];
        $pass = $data['password'];

        // tirando possíveis @ do nome de usuário/email
        if(str_starts_with($user, '@')) {
            $user = str_replace('@', '', $user);
        }

        // procurando dados no banco
        try {
            $query = $conn->prepare('SELECT * FROM usuarios WHERE username = :username OR email = :email');
            $query->bindParam(':username', $user, PDO::PARAM_STR);
            $query->bindParam(':email', $user, PDO::PARAM_STR);
            $query->execute();

        } catch (PDOException $err) {
            echo generate_response(false, 'PDO_ERROR', $err->getMessage(), $data);
            exit;
        }

        $found_data = $query->fetchAll(PDO::FETCH_ASSOC);


        if($found_data) {
            
            // verificando senha antes de tudo, se não bater, eu quebro a requisição
            if(!password_verify($pass, $found_data[0]['password'])){
                echo generate_response(false, 'INVALID_PASSWORD', 'Senhas não batem.', $found_data[0]);
                exit;
            }

            // criando sessão se não existir; se já existe, eu renovo
            if(!isset($_COOKIE[SESSION_COOKIE_NAME])) {
                create_session($conn, $found_data[0]['id'], 3);
            } else {
                remove_session($conn, $_COOKIE[SESSION_COOKIE_NAME]);
                create_session($conn, $found_data[0]['id'], 3);
            }
            
            echo generate_response(true, 'OK', 'Login bem-sucedido. Renovando sessão.', $found_data[0]);
            exit;

        } else {
            
            echo generate_response(false, 'NOT_FOUND', 'Dados não foram encontrados no banco.');
            exit;
        }

    } else {
        echo generate_response(false, 'NOT_FOUND', 'Não foi enviado nenhum dado ao servidor.');
        exit;
    }
?>