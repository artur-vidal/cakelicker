<?php

    require_once '../utils/functions.php';

    $dbhost = 'localhost';
    $dbschema = 'cakelicker_database';
    $dbuser = 'root';
    $dbpassword = '';

    try {

        // criando objeto
        $conn = new PDO("mysql:host=$dbhost;dbname=$dbschema;charset=utf8", $dbuser, $dbpassword);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // criando tabelas caso precise
        $query_content = file_get_contents('../../database/schema.sql');
        $conn->exec($query_content);

    } catch (PDOException $err) {
        echo generate_response(false, 'PDO_ERROR', $err->getMessage());
        exit;
    }

?>