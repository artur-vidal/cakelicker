<?php

    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../utils/functions.php';

    $data = json_decode(file_get_contents('php://input'), true);

    // regex de username (deve ter até 20 caracteres minusculos, numeros, sem espaço, e apenas _ como caractere especial)
    $username_expression = '/^[a-z0-9_]{3,20}(?=[a-z])/';
    if(!preg_match($username_expression, $data['username'])) {
        http_response_code(401);
        echo generate_response(false, 'INVALID_USERNAME', 'Nome de usuário só pode ter letras minúsculas, números e nenhum espaço, ao menos 4 caracteres.', $data);
        exit;
    }

    // senha - pelo menos 8 caracteres e com número
    $password_expression = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';
    if(!preg_match($password_expression, $data['password'])) {
        http_response_code(401);
        echo generate_response(false, 'INVALID_PASSWORD', 'Senha precisa ter ao menos uma letra maiúscula, uma minúscula, um dígito e mínimo de 8 caracteres.', $data);
        exit;
    } 

    // data de nascimento (formato YYYY-MM-DD)
    $dtnasc_expression = '/^\d{4}-\d{2}-\d{2}$/'; // Exemplo de regex para a data
    if(!preg_match($dtnasc_expression, $data['birthdate'])) {
        http_response_code(401);
        echo generate_response(false, 'INVALID_BIRTHDATE', 'Data de nascimento tem que estar em formato YYYY-MM-DD.', $data);
        exit;
    }

    // email (exemplo@email.com)
    $email_expression = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    if(!preg_match($email_expression, $data['email'])) {
        http_response_code(401);
        echo generate_response(false, 'INVALID_EMAIL', 'E-mail precisa seguir formato padrão.', $data);
        exit;
    }

    // se passar pela validação base, verifico se já existe usuário com esse user ou email🔥
    try {
        $same_user_query = $conn->prepare('SELECT username FROM usuarios WHERE username = :username OR email = :email');
        $same_user_query->bindParam(':username', $data['username'], PDO::PARAM_STR);
        $same_user_query->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $same_user_query->execute();
    } catch (PDOException $err){
        http_response_code(500);
        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        exit;
    }

    $same_user = $same_user_query->fetch(PDO::FETCH_ASSOC);

    if($same_user) {
        http_response_code(409);
        echo generate_response(false, 'USER_EXISTS', 'Já existe um usuário com esse @ ou e-mail registrado.', $data);
        exit;
    }

    // se ainda passar por essa, registro os datas no databank
    // crio um arquivo e uma entrada no banco antes
    $upload_dir = '../uploads/';
    if(!is_dir($upload_dir)){
        mkdir($upload_dir);
    }

    try {
        $filename = $upload_dir . bin2hex(random_bytes(16)) . '_savefile.json';
        file_put_contents($filename, json_encode([]));
    } catch (Exception $err) {
        http_response_code(500);
        echo generate_response(false, 'FILE_ERROR', $err->getMessage(), $data);
        exit;
    }
    
    try {
        $save_stmt = $conn->prepare('INSERT INTO saves(name, cakes, xp, level, prestige, rebirths, savepath) VALUES("Desconhecido", "0", 0, 1, 0, 0, :savepath)');
        $save_stmt->bindParam(':savepath', $filename, PDO::PARAM_STR);
        $save_stmt->execute();
    } catch (PDOException $err) {
        http_response_code(500);
        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        exit;
    }

    // eu busco o id do save recém criado
    try {
        $save_query = $conn->prepare('SELECT id FROM saves ORDER BY dtcriacao DESC LIMIT 1');
        $save_query->execute();
        $save_id = $save_query->fetchAll(PDO::FETCH_ASSOC)[0]['id'];
    } catch (PDOException $err) {
        http_response_code(500);
        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        exit;
    }

    // crio um usuário ligado ao id desse save
    try {

        $encrypted_password = password_hash($data['password'], PASSWORD_BCRYPT);

        $user_stmt = $conn->prepare('INSERT INTO usuarios(username, email, password, nickname, idsave, dtnasc) VALUES(:username, :email, :password, :nickname, :idsave, :dtnasc)');
        $user_stmt->bindParam(':username', $data['username'], PDO::PARAM_STR);
        $user_stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $user_stmt->bindParam(':password', $encrypted_password, PDO::PARAM_STR);
        $user_stmt->bindParam(':nickname', $data['nickname'], PDO::PARAM_STR);
        $user_stmt->bindParam(':idsave', $save_id, PDO::PARAM_INT);
        $user_stmt->bindParam(':dtnasc', $data['birthdate'], PDO::PARAM_STR);

        // executando registramento épico !!!!1!1! !! !  1!1!
        $user_stmt->execute();
    } catch (PDOException $err) {
        http_response_code(500);
        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        exit;
    }
    
    // voltando com uma resposta falano qui deu serto
    http_response_code(201);
    echo generate_response(true, 'OK', 'Sucesso no registro!');
?>