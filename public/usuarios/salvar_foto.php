<?php

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {

    http_response_code(401);

    echo json_encode([
        'erro' => 'Não autorizado'
    ]);

    exit;
}

require_once __DIR__ . '/../../config/db_connect.php';

$database = new Database();

$pdo = $database->getConnection();

$arquivo = $_FILES['foto'] ?? null;

if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {

    http_response_code(400);

    echo json_encode([
        'erro' => 'Arquivo inválido'
    ]);

    exit;
}

$tiposPermitidos = [
    'image/jpeg',
    'image/png',
    'image/webp'
];

$tipo = mime_content_type($arquivo['tmp_name']);

if (!in_array($tipo, $tiposPermitidos)) {

    http_response_code(400);

    echo json_encode([
        'erro' => 'Tipo não permitido. Use JPG, PNG ou WEBP.'
    ]);

    exit;
}

// Caminho físico da pasta
$pasta = __DIR__ . '/../assets/fotoperfil/';

if (!is_dir($pasta)) {

    mkdir($pasta, 0755, true);
}

// Define extensão
$extensoes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$extensao = $extensoes[$tipo];

$nomeArquivo =
    'usuario_' .
    (int) $_SESSION['usuario_id'] .
    '.' .
    $extensao;

$destino = $pasta . $nomeArquivo;


foreach (['jpg', 'png', 'webp'] as $ext) {

    $arquivoAntigo =
        $pasta .
        'usuario_' .
        (int) $_SESSION['usuario_id'] .
        '.' .
        $ext;

    if (
        file_exists($arquivoAntigo) &&
        $ext !== $extensao
    ) {

        unlink($arquivoAntigo);
    }
}

// Move arquivo
if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {

    http_response_code(500);

    echo json_encode([
        'erro' => 'Falha ao salvar o arquivo.'
    ]);

    exit;
}

$urlPublica =
    '/DriverLux/public/assets/fotoperfil/' .
    $nomeArquivo;

try {

    $stmt = $pdo->prepare(
        "UPDATE usuarios
         SET foto_perfil = :foto
         WHERE id = :id"
    );

    $stmt->execute([
        ':foto' => $urlPublica,
        ':id'   => (int) $_SESSION['usuario_id']
    ]);

    echo json_encode([
        'status' => 'success',
        'url'    => $urlPublica
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'erro' => 'Erro no banco de dados.'
    ]);
}