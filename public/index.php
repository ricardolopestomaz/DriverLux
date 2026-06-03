<?php

session_start();

define('ROOT_PATH', dirname(__DIR__));

// ==========================================
// URI
// ==========================================
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove as pastas base da URL
$request = str_replace('/DriverLux/public', '', $request);
$request = str_replace('/DriverLux', '', $request);

$request = trim($request, '/');

$uri = explode('/', $request);

// ==========================================
// API
// ==========================================
if (isset($uri[0]) && $uri[0] === 'api') {

    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    $resource = $uri[1] ?? null;

    $subResource = $uri[2] ?? null;

    $id = is_numeric($subResource)
        ? (int)$subResource
        : null;

    $action = !is_numeric($subResource)
        ? $subResource
        : null;

    switch ($resource) {

        // ==========================================
        // USUÁRIOS
        // ==========================================
        case 'usuarios':

            require_once ROOT_PATH . '/app/Controller/UsuarioController.php';

            $controller = new UsuarioController();

            if ($action === 'login') {

                $controller->login($method);

            } elseif ($action === 'logout') {

                session_destroy();

                echo json_encode([
                    "status" => "success",
                    "mensagem" => "Logout realizado."
                ]);

            } else {

                $controller->handleRequest($method, $id);
            }

            break;

        // ==========================================
        // VEÍCULOS
        // ==========================================
        case 'veiculos':

            require_once ROOT_PATH . '/app/Controller/VeiculoController.php';

            $controller = new VeiculoController();

            $controller->handleRequest($method, $id);

            break;

        // ==========================================
        // CATEGORIAS
        // ==========================================
        case 'categorias':

            require_once ROOT_PATH . '/app/Controller/CategoriaController.php';

            $controller = new CategoriaController();

            $controller->handleRequest($method, $id);

            break;

        // ==========================================
        // PROTEÇÕES
        // ==========================================
        case 'protecoes':

            require_once ROOT_PATH . '/app/Controller/ProtecaoController.php';

            $controller = new ProtecaoController();

            $controller->handleRequest($method, $id);

            break;

        // ==========================================
        // QUILOMETRAGEM
        // ==========================================
        case 'km':

            require_once ROOT_PATH . '/app/Controller/KMController.php';

            $controller = new KMController();

            $controller->handleRequest($method, $id);

            break;

        // ==========================================
        // CUPONS
        // ==========================================
        case 'cupons':

            require_once ROOT_PATH . '/app/Controller/CupomController.php';

            $controller = new CupomController();

            $param = ($id !== null) ? $id : $action;

            $controller->handleRequest($method, $param);

            break;

        // ==========================================
        // PAGAMENTOS
        // ==========================================
        case 'pagamentos':

            require_once ROOT_PATH . '/app/Controller/PagamentoController.php';

            $controller = new PagamentoController();

            $controller->handleRequest($method, $id);

            break;

        // ==========================================
        // RESERVAS
        // ==========================================
        case 'reservas':

            require_once ROOT_PATH . '/app/Controller/ReservaController.php';

            $controller = new ReservaController();

            // Rota especial: GET /api/reservas/minhas (reservas do usuário logado)
            $controller->handleRequest($method, $id, $action);

            break;

        // ==========================================
        // NÃO ENCONTRADO
        // ==========================================
        default:

            http_response_code(404);

            echo json_encode([
                "erro" => "Endpoint não encontrado."
            ]);
    }

    exit;
}

// ==========================================
// SITE
// ==========================================
$page = $uri[0] ?? '';

// 1. Redirecionamento automático para a Home
if ($page === '' || $page === 'index') {
    header('Location: /DriverLux/index.html');
    exit;
}

// 2. Lógica normal para outras páginas
$publicPath = ROOT_PATH . '/public/';
$htmlFile = $publicPath . $page . '.html';
$phpFile = $publicPath . $page . '.php';

// Página PHP
if (file_exists($phpFile)) {
    require_once $phpFile;
    exit;
}

// Página HTML
if (file_exists($htmlFile)) {
    readfile($htmlFile);
    exit;
}

// 404
http_response_code(404);
echo "<h1>404</h1>";
echo "<p>Página não encontrada.</p>";