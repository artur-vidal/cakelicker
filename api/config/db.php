<?php

    use Cakelicker\Helpers\ResponseHelper;
    
    $dbhost = $_ENV['DB_HOST'] . ':' . $_ENV['DB_PORT'];
    $dbuser = $_ENV['DB_USER'];
    $dbpassword = $_ENV['DB_PASSWORD'];

    try {
        
        // criando objeto
        $conn = new PDO("mysql:host=$dbhost;charset=utf8", $dbuser, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // criando tabelas caso precise
        $query_content = file_get_contents(__DIR__ .'\\..\\database\\schema.sql');
        $conn->exec($query_content);

    } catch (PDOException $err) {
        echo json_encode(ResponseHelper::generate(false, 500, 'Ocorreu um erro na conexão com o banco de dados.', $err->getMessage()));
        exit;
    }

?>