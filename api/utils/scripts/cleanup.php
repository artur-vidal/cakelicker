<?php

    require_once __DIR__ . '\\..\\..\\init.php';

    echo (string) remove_useless_saves($conn) . ' arquivos removidos!';

?>