<?php
// Pequeno loader para o painel admin que existe fora da pasta public
// Mantém compatibilidade com a URL /DriverLux/admin.php

$viewPath = __DIR__ . '/../app/View/Painel do admin/admin.php';

if (file_exists($viewPath)) {
    require_once $viewPath;
    exit;
}

http_response_code(404);
echo "<h1>404</h1><p>Página não encontrada.</p>";
