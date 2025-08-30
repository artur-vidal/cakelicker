<?php

    require_once '../config/db.php';
    require_once '../utils/functions.php';

    // pegando o token e saindo se não tiver nada
    $token = $_COOKIE[SESSION_COOKIE_NAME] ?? null;
    if(!$token) {
        http_response_code(404);
        echo generate_response(false, 'NOT_FOUND', 'Token não foi encontrado nos cookies.');
        exit;
    }

    // rodo a query, expiro e respondo
    try {
        $delete_session = $conn->prepare('DELETE FROM sessions WHERE token = :token');
        $delete_session->bindParam(':token', $token, PDO::PARAM_INT);
        $delete_session->execute();

        setcookie(SESSION_COOKIE_NAME, '', time() - 3600);

        http_response_code(200);
        echo generate_response(true, 'OK', 'Sessão encontrada e removida com sucesso.');
        exit;
    } catch(PDOException $err) {
        http_response_code(500);
        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        exit;
    }

?>