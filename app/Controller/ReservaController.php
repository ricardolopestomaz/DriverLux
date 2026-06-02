<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Model/ReservaModel.php';
require_once __DIR__ . '/../Service/ReservaService.php';


class ReservaController {
    private $service;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        
        $model = new ReservaModel($db);
        $this->service = new ReservaService($model);
    }

    public function handleRequest($method, $id, $action = null) {
        if ($method === 'GET') {
            if ($action === 'minhas') {
                // GET /api/reservas/minhas — reservas do usuário logado
                $this->verificarAutenticacao();
                $response = $this->service->listarReservasPorUsuario($_SESSION['usuario_id']);
            } elseif ($id) {
                $response = $this->service->buscarReserva($id);
            } else {
                $response = $this->service->listarReservas();
            }
            $this->sendResponse($response);
            
        } elseif ($method === 'POST') {
            $this->verificarAutenticacao();
            $data = json_decode(file_get_contents("php://input"));
            
            // Pega o ID do usuário diretamente da Sessão
            $id_do_usuario_logado = $_SESSION['usuario_id'];
            
            $response = $this->service->criarReserva($data, $id_do_usuario_logado);
            $this->sendResponse($response);
            
        } elseif ($method === 'PUT') {
            // Adicionado bloqueio no PUT também, caso precise garantir que só logados atualizem
            $this->verificarAutenticacao(); 
            
            $data = json_decode(file_get_contents("php://input"));
            $response = $this->service->atualizarReserva($id, $data);
            $this->sendResponse($response);
            
        } else {
            $this->sendResponse([
                "status_code" => 405, 
                "body" => ["erro" => "Método HTTP não permitido."]
            ]);
        }
    }

    // Centraliza o disparo das respostas JSON
    private function sendResponse($response) {
        http_response_code($response['status_code']);
        echo json_encode($response['body']);
    }

    // SEGURANÇA E AUTORIZAÇÃO (Nível Cliente)
    private function verificarAutenticacao() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->sendResponse([
                "status_code" => 401,
                "body" => [
                    "status" => "error", 
                    "erro" => "Acesso negado. Você precisa fazer login para interagir com reservas!"
                ]
            ]);
            exit; 
        }
    }
}
?>