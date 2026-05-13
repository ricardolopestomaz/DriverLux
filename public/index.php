<?php
// /public/index.php

// 1. Defina a raiz do projeto
define('ROOT_PATH', dirname(__DIR__));

// 2. Captura e limpa a URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

// Remove 'DriverLux' e 'public' da jogada para não confundir o roteador
if (isset($uri[0]) && strtolower($uri[0]) === 'driverlux') array_shift($uri);
if (isset($uri[0]) && strtolower($uri[0]) === 'public') array_shift($uri);

$isApi = isset($uri[0]) && $uri[0] === 'api';

if ($isApi) {
    // ==========================================
    // MODO API
    // ==========================================
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

    $method = $_SERVER['REQUEST_METHOD'];
    $resource = isset($uri[1]) ? $uri[1] : null; 
    $subResource = isset($uri[2]) ? $uri[2] : null;
    
    $id = is_numeric($subResource) ? (int)$subResource : null;
    $action = !is_numeric($subResource) ? $subResource : null;

    switch ($resource) {
        case 'usuarios':
            require_once ROOT_PATH . '/modules/Usuarios/UsuarioController.php';
            $controller = new UsuarioController();

            if ($action === 'login') {
                $controller->login($method);
 
            } elseif ($action === 'logout') {
            session_start();
            session_unset();
            session_destroy();

            http_response_code(200);
             echo json_encode([
                "status" => "success",
                "mensagem" => "Logout realizado com sucesso."
            ], JSON_UNESCAPED_UNICODE);

            } elseif ($action === 'me') {
                $controller->handleRequest($method, null);

            } elseif ($action === null || is_numeric($subResource)) {
                $controller->handleRequest($method, $id);

            } else {
                 http_response_code(404);
                echo json_encode(["erro" => "Ação não encontrada."], JSON_UNESCAPED_UNICODE);
            }
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
    // MODO SITE (Retorna páginas HTML/PHP)
    // ==========================================
    header("Content-Type: text/html; charset=UTF-8");
    
    $page = !empty($uri[0]) ? $uri[0] : 'home';
    
    // Usando DIRECTORY_SEPARATOR para garantir que o Windows entenda o caminho
    $basePath = ROOT_PATH . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR;
    $filePhp = $basePath . $page . ".php";
    $fileHtml = $basePath . $page . ".html";

    if (file_exists($filePhp)) {
        require_once $filePhp;
    } elseif (file_exists($fileHtml)) {
        readfile($fileHtml);
    } else {
        http_response_code(404);
        $file404 = $basePath . "404.php";
        
        if (file_exists($file404)) {
            require_once $file404;
        } else {
            echo "<h1>404 ;-;</h1>";
            echo "<p>Página <strong>" . htmlspecialchars($page) . "</strong> não encontrada.</p>";
            echo "<a href='/DriverLux/home'>Voltar para o início</a>";
        }
    }
}