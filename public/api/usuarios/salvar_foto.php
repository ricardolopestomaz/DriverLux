<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

require_once __DIR__ . '/../../../config/db_connect.php';
$database = new Database();
$pdo = $database->getConnection();

$arquivo = $_FILES['foto'] ?? null;
if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['erro' => 'Arquivo inválido']);
    exit;
}

$tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
$tipo = mime_content_type($arquivo['tmp_name']);
if (!in_array($tipo, $tiposPermitidos)) {
    echo json_encode(['erro' => 'Tipo não permitido. Use JPG, PNG ou WEBP.']);
    exit;
}

// CORREÇÃO: Voltando duas pastas para sair de api/usuarios e achar a assets correta
$pasta = __DIR__ . '/../../assets/fotoperfil/';
if (!is_dir($pasta)) {
    mkdir($pasta, 0755, true);
}

$extensao = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$tipo];
$nomeArquivo = 'usuario_' . (int)$_SESSION['usuario_id'] . '.' . $extensao;
$destino = $pasta . $nomeArquivo;

// Limpa extensões antigas para evitar arquivos duplicados se o usuário mudar o formato
foreach (['jpg', 'png', 'webp'] as $ext) {
    $arquivoAntigo = $pasta . 'usuario_' . (int)$_SESSION['usuario_id'] . '.' . $ext;
    if (file_exists($arquivoAntigo) && $ext !== $extensao) {
        @unlink($arquivoAntigo);
    }
}

if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
    echo json_encode(['erro' => 'Falha ao salvar o arquivo no servidor.']);
    exit;
}

$urlPublica = '/DriverLux/public/assets/fotoperfil/' . $nomeArquivo;

try {
    $stmt = $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
    $stmt->execute([$urlPublica, (int)$_SESSION['usuario_id']]);
    echo json_encode(['status' => 'ok', 'url' => $urlPublica]);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro no banco: ' . $e->getMessage()]);
}