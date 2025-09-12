<?php

    require_once __DIR__ . "/../config/db.php";
    require_once __DIR__ . "/../utils/functions.php";

    // token enviado pelo JS
    $token = json_decode(file_get_contents('php://input'), true)['token'];

    // procurando no banco e retornando informações do usuário
    try {
        $query = $conn->prepare('SELECT usuarios.* FROM usuarios JOIN sessions ON usuarios.id = sessions.idusuario WHERE sessions.token = :token');
        $query->bindParam(':token', $token, PDO::PARAM_STR);
        $query->execute();

        $found_data = $query->fetchAll(PDO::FETCH_ASSOC);

        if($found_data) {
            echo generate_response(true, 'OK', 'Dados da sessão recebidos.', $found_data[0]);
            exit();
        } else {
            echo generate_response(true, 'NOT_FOUND', 'Dados da sessão não foram encontrados.');
            exit();
        }

    } catch(PDOException $err) {
        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        exit();
    }

?>