<?php

define('ROOT_PATH', dirname(__DIR__));

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$baseFolder = '/DriverLux/public';

$request = str_replace($baseFolder, '', $request);
$request = trim($request, '/');

$uri = $request === '' ? [] : explode('/', $request);

/* LIBERAR CSS, JS E IMAGENS */
$arquivoEstatico = ROOT_PATH . '/public/' . $request;

if ($request !== '' && file_exists($arquivoEstatico) && is_file($arquivoEstatico)) {
    $ext = pathinfo($arquivoEstatico, PATHINFO_EXTENSION);

    $tipos = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml'
    ];

    if (isset($tipos[$ext])) {
        header('Content-Type: ' . $tipos[$ext]);
    }

    readfile($arquivoEstatico);
    exit;
}

/* API */
if (isset($uri[0]) && $uri[0] === 'api') {
    header("Content-Type: application/json; charset=UTF-8");

    $method = $_SERVER['REQUEST_METHOD'];
    $resource = $uri[1] ?? null;
    $subResource = $uri[2] ?? null;

    $id = is_numeric($subResource) ? (int)$subResource : null;
    $action = !is_numeric($subResource) ? $subResource : null;

    switch ($resource) {
        case 'usuarios':
            require_once ROOT_PATH . '/app/Controller/UsuarioController.php';
            $controller = new UsuarioController();

            if ($action === 'login') {
                $controller->login($method);
            } elseif ($action === 'logout') {
                session_start();
                session_destroy();
                echo json_encode(["status" => "success"]);
            } else {
                $controller->handleRequest($method, $id);
            }
            break;

        case 'veiculos':
            require_once ROOT_PATH . '/app/Controller/VeiculoController.php';
            (new VeiculoController())->handleRequest($method, $id);
            break;

        case 'protecoes':
            require_once ROOT_PATH . '/app/Controller/ProtecaoController.php';
            (new ProtecaoController())->handleRequest($method, $id);
            break;

        case 'km':
            require_once ROOT_PATH . '/app/Controller/KMController.php';
            (new KMController())->handleRequest($method, $id);
            break;

        case 'reservas':
            require_once ROOT_PATH . '/app/Controller/ReservaController.php';
            (new ReservaController())->handleRequest($method, $id);
            break;

        default:
            http_response_code(404);
            echo json_encode(["erro" => "Endpoint não encontrado."]);
    }

    exit;
}

/* SITE */
header("Content-Type: text/html; charset=UTF-8");

$page = $uri[0] ?? 'home';

$basePath = ROOT_PATH . '/app/View/';

$filePhp = $basePath . $page . '.php';
$fileHtml = $basePath . $page . '.html';

if (file_exists($filePhp)) {
    require_once $filePhp;
    exit;
}

if (file_exists($fileHtml)) {
    readfile($fileHtml);
    exit;
}

http_response_code(404);
echo "<h1>DESCULPE</h1>";
echo "<p>não conseguimos encontrar esta página</p>";
echo "<a href='/DriverLux/public/'>a página inicial da Driverlux</a>";