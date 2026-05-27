<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Model/VeiculoModel.php';
require_once __DIR__ . '/../Service/VeiculoService.php';

class VeiculoController {
    private $service;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        
        // Injeção de dependência simplificada
        $model = new VeiculoModel($db);
        $this->service = new VeiculoService($model);
    }

    public function handleRequest($method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $response = $this->service->buscarVeiculo($id);
                } else {
                    $response = $this->service->listarVeiculos();
                }
                $this->sendResponse($response);
                break;

            case 'POST':
                $this->verificarAcessoAdmin();
                $data = json_decode(file_get_contents("php://input"));
                $response = $this->service->criarVeiculo($data);
                $this->sendResponse($response);
                break;

            case 'PUT':
                $this->verificarAcessoAdmin();
                $data = json_decode(file_get_contents("php://input"));
                $response = $this->service->atualizarVeiculo($id, $data);
                $this->sendResponse($response);
                break;

            default:
                $this->sendResponse([
                    "status_code" => 405, 
                    "body" => ["erro" => "Método HTTP não permitido."]
                ]);
                break;
        }
    }

    // Método auxiliar para centralizar a saída (echo e headers)
    private function sendResponse($response) {
        http_response_code($response['status_code']);
        echo json_encode($response['body']);
    }

    // SEGURANÇA E AUTORIZAÇÃO
    private function verificarAcessoAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            $this->sendResponse([
                "status_code" => 401,
                "body" => ["status" => "error", "erro" => "Acesso negado. Você precisa fazer login primeiro!"]
            ]);
            exit; 
        }

        if ($_SESSION['usuario_perfil'] !== 'admin') {
            $this->sendResponse([
                "status_code" => 403,
                "body" => ["status" => "error", "erro" => "Acesso negado. Apenas administradores podem gerenciar a frota."]
            ]);
            exit;
        }
    }
}
?>