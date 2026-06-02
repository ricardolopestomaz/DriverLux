<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Model/CategoriaModel.php';
require_once __DIR__ . '/../Service/CategoriaService.php';

class CategoriaController {

    private $service;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $model = new CategoriaModel($db);
        $this->service = new CategoriaService($model);
    }

    public function handleRequest($method, $id = null) {

        switch ($method) {

            case 'GET':
                $resultado = $this->service->listarCategorias();
                break;

            case 'POST':
                $this->verificarAcessoAdmin();
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->criarCategoria($data);
                break;

            case 'PUT':
                $this->verificarAcessoAdmin();
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->atualizarCategoria($id, $data);
                break;

            default:
                $this->sendResponse([
                    "status_code" => 405,
                    "body" => ["erro" => "Método HTTP não permitido."]
                ]);
                return;
        }

        $this->sendResponse($resultado);
    }

    private function sendResponse($response) {
        http_response_code($response['status_code']);
        echo json_encode($response['body'], JSON_UNESCAPED_UNICODE);
    }

    private function verificarAcessoAdmin() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->sendResponse([
                "status_code" => 401,
                "body" => ["erro" => "Acesso negado. Faça login primeiro."]
            ]);
            exit;
        }

        if ($_SESSION['usuario_perfil'] !== 'admin') {
            $this->sendResponse([
                "status_code" => 403,
                "body" => ["erro" => "Acesso negado. Apenas administradores podem realizar esta operação."]
            ]);
            exit;
        }
    }
}