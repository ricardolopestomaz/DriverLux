<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Model/PagamentoModel.php';
require_once __DIR__ . '/../Service/PagamentoService.php';

class PagamentoController {

    private $service;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $model = new PagamentoModel($db);
        $this->service = new PagamentoService($model);
    }

    public function handleRequest($method, $id = null) {

        switch ($method) {

            case 'GET':
                if ($id) {
                    $resultado = $this->service->buscarPagamento($id);
                } else {
                    $resultado = $this->service->listarPagamentos();
                }
                break;

            case 'POST':
                $this->verificarAutenticacao();
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->criarPagamento($data);
                break;

            case 'PUT':
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->atualizarPagamento($id, $data);
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

    private function verificarAutenticacao() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->sendResponse([
                "status_code" => 401,
                "body" => ["erro" => "Acesso negado. Faça login primeiro."]
            ]);
            exit;
        }
    }
}