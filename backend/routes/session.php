<?php

    require_once '../config/db.php';
    require_once '../utils/functions.php';

    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // pegando cookiezinho !!! :P
            $token = $_COOKIE[SESSION_COOKIE_NAME] ?? null;

            // se não existir token de sessão, já cancelo a função, se liga meu irmão 😎
            if(!$token) {
                http_response_code(401);
                echo generate_response(false, 'NO_SESSION', 'Cookie de sessão não encontrado.');
                exit;
            }

            // procurando no banco e retornando id e data
            try {
                $query = $conn->prepare('SELECT idusuario, expiresat FROM sessions WHERE token = :token');
                $query->bindParam(':token', $token, PDO::PARAM_STR);
                $query->execute();
            } catch(PDOException $err) {
                http_response_code(500);
                echo generate_response(false, 'PDO_ERROR', $err->getMessage());
                exit;
            }

            
            $session_info = $query->fetchAll(PDO::FETCH_ASSOC);

            if($session_info) {
                // vendo se a sessão não expirou ainda - se tiver expirado, deleto e retorno resultado
                if(strtotime($session_info[0]['expiresat']) < time()) {

                    try {
                        $del_session = $conn->prepare('DELETE FROM sessions WHERE token = :token');
                        $del_session->bindParam(':token', $token, PDO::PARAM_STR);

                        http_response_code(401);
                        echo generate_response(false, 'SESSION_EXPIRED', 'Esta sessão já expirou, e foi removida do banco.');
                        exit;
                    } catch (PDOException $err) {
                        http_response_code(500);
                        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
                        exit;
                    }
                }

                // até aqui deu tudo certo, então vou pegar os dados do usuário e retornar pro cliente
                try {
                    $user_query = $conn->prepare('SELECT * FROM usuarios WHERE id = :id');
                    $user_query->bindParam(':id', $session_info[0]['idusuario'], PDO::PARAM_INT);
                    $user_query->execute();
                } catch (PDOException $err) {
                    http_response_code(500);
                    echo generate_response(true, 'PDO_ERROR', $err->getMessage());
                    exit;
                }

                $found_data = $user_query->fetchAll(PDO::FETCH_ASSOC);
                
                if($found_data) {
                    http_response_code(200);
                    echo generate_response(true, 'OK', 'Sessão encontrada e dados do usuário recebidos.', $found_data[0]);
                    exit;
                } else {
                    http_response_code(404);
                    echo generate_response(false, 'NOT_FOUND', 'Sessão encontrada, mas usuário não existe.');
                    exit;
                }
            } else {
                http_response_code(404);
                echo generate_response(true, 'NOT_FOUND', 'Dados da sessão não foram encontrados.');
                exit;
            }
            break;

        case 'DELETE':
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
            break;
    }

?>