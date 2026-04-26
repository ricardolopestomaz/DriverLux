<?php
// /public/index.php

// 1. Defina a raiz do projeto para facilitar os requires
define('ROOT_PATH', dirname(__DIR__));

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

if (isset($uri[0]) && strtolower($uri[0]) === 'driverlux') array_shift($uri);
if (isset($uri[0]) && strtolower($uri[0]) === 'public') array_shift($uri);

$isApi = isset($uri[0]) && $uri[0] === 'api';

if ($isApi) {
    // MODO API (Retorna apenas JSON)
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

    $method = $_SERVER['REQUEST_METHOD'];
    $resource = isset($uri[1]) ? $uri[1] : null; 
    $id = isset($uri[2]) ? (int)$uri[2] : null;

    switch ($resource) {
        case 'usuarios':
            require_once ROOT_PATH . '/modules/Usuarios/UsuarioController.php';
            $controller = new UsuarioController();
            $controller->handleRequest($method, $id);
            break;
            
        case 'veiculos':
            require_once ROOT_PATH . '/modules/Veiculos/VeiculoController.php';
            $controller = new VeiculoController();
            $controller->handleRequest($method, $id);
            break;
        case 'categorias':
            require_once ROOT_PATH . '/modules/Categorias/CategoriaController.php';
            $controller = new CategoriaController();
            $controller->handleRequest($method, $id);
            break;

        case 'protecoes':
            require_once ROOT_PATH . '/modules/Protecoes/ProtecaoController.php';
            $controller = new ProtecaoController();
            $controller->handleRequest($method, $id);
            break;

        case 'km':
            require_once ROOT_PATH . '/modules/Quilometragem/KMController.php';
            $controller = new KMController();
            $controller->handleRequest($method, $id);
            break;
        case 'cupons':
            require_once ROOT_PATH . '/modules/Cupons/CupomController.php';
            $controller = new CupomController();
            $controller->handleRequest($method, $id);
            break;

        case 'reservas':
            require_once ROOT_PATH . '/modules/Reservas/ReservaController.php';
            $controller = new ReservaController();
            $controller->handleRequest($method, $id);
            break;

        default:
            http_response_code(404);
            echo json_encode(["mensagem" => "Endpoint da API não encontrado."]);
            break;
    }

} else {
    // ==========================================
    // MODO SITE (Retorna páginas HTML)
    // ==========================================
    header("Content-Type: text/html; charset=UTF-8");
    
    $page = !empty($uri[0]) ? $uri[0] : 'home';

    switch ($page) {
        case 'home':
            require_once ROOT_PATH . '/views/home.php';
            break;
            
        case 'sobre':
            require_once ROOT_PATH . '/views/sobre.php';
            break;
            
        default:
            http_response_code(404);
            // Garante que o arquivo existe antes de chamar
            $file404 = ROOT_PATH . '/views/404.php';
            if (file_exists($file404)) {
                require_once $file404;
            } else {
                echo "<h1>404 Not Found</h1><p>Crie o arquivo /views/404.php para personalizar esta tela.</p>";
            }
            break;
    }
}
?>