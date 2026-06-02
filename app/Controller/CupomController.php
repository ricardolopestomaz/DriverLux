<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Model/CupomModel.php';
require_once __DIR__ . '/../Service/CupomService.php';

class CupomController {

    private $service;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $model = new CupomModel($db);
        $this->service = new CupomService($model);
    }

    public function handleRequest($method, $id = null) {

        switch ($method) {

            case 'GET':
                if ($id !== null && !is_numeric($id)) {
                    $resultado = $this->service->buscarPorCodigo($id);
                } else {
                    $resultado = $this->service->listarCupons();
                }
                break;

            case 'POST':
                $this->verificarAcessoAdmin();
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->criarCupom($data);
                break;

            case 'PUT':
                $this->verificarAcessoAdmin();
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->atualizarCupom($id, $data);
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
        echo json_encode($response['body']);
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