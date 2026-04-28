<?php
// views/404.php
http_response_code(404); // Garante que o navegador saiba que é um erro
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>404 - Não Encontrado</title>
</head>
<body>
    <h1>DESCULPE</h1>
    <p>não conseguimos encontrar esta página</p>
    <p>Pesquise novamente ou volte para</p>
    
    <a href="/DriverLux/home" class="btn">a página inicial da Driverlux</a>
</body>
</html>