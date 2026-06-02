<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Model/KMModel.php';
require_once __DIR__ . '/../Service/KMService.php';


class KMController {
    private $service;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $model = new KMModel($db);
        $this->service = new KMService($model);
    }

    public function handleRequest($method, $id) {
        if ($method === 'GET') {
            $this->getOpcoesKM();
        } elseif ($method === 'POST') {
            $this->verificarAcessoAdmin();
            $this->createOpcaoKM();
        } elseif ($method === 'PUT') {
            $this->verificarAcessoAdmin();
            $this->updateOpcaoKM($id);
        } else {
            $this->sendResponse([
                "status_code" => 405,
                "body" => ["erro" => "Método HTTP não permitido."]
            ]);
        }
    }

    private function getOpcoesKM() {
        $resultado = $this->service->listarOpcoes();
        $this->sendResponse($resultado);
    }

    private function createOpcaoKM() {
        $data = json_decode(file_get_contents("php://input"));
        $resultado = $this->service->criarOpcao($data);
        $this->sendResponse($resultado);
    }

    private function updateOpcaoKM($id) {
        $data = json_decode(file_get_contents("php://input"));
        $resultado = $this->service->atualizarOpcao($id, $data);
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
                "body" => [
                    "status" => "error",
                    "erro" => "Acesso negado. Você precisa fazer login primeiro!"
                ]
            ]);
            exit;
        }

        if ($_SESSION['usuario_perfil'] !== 'admin') {
            $this->sendResponse([
                "status_code" => 403,
                "body" => [
                    "status" => "error",
                    "erro" => "Acesso negado. Apenas administradores podem gerenciar planos de quilometragem."
                ]
            ]);
            exit;
        }
    }
}