<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Service/KMService.php';


class KMController {
    private $service;

    public function __construct() {
        $db = (new Database())->getConnection();
        $this->service = new KMService($db);
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
            http_response_code(405);
            echo json_encode(["erro" => "Método HTTP não permitido."]);
        }
    }

    private function getOpcoesKM() {
        $opcoes = $this->service->listarOpcoes();

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "total" => count($opcoes),
            "data" => $opcoes
        ]);
    }

    private function createOpcaoKM() {
        $data = json_decode(file_get_contents("php://input"));

        $resultado = $this->service->criarOpcao($data);

        http_response_code($resultado["status"]);
        echo json_encode($resultado["resposta"]);
    }

    private function updateOpcaoKM($id) {
        $data = json_decode(file_get_contents("php://input"));

        $resultado = $this->service->atualizarOpcao($id, $data);

        http_response_code($resultado["status"]);
        echo json_encode($resultado["resposta"]);
    }

    private function verificarAcessoAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "erro" => "Acesso negado. Você precisa fazer login primeiro!"
            ]);
            exit;
        }

        if ($_SESSION['usuario_perfil'] !== 'admin') {
            http_response_code(403);
            echo json_encode([
                "status" => "error",
                "erro" => "Acesso negado. Apenas administradores podem gerenciar planos de quilometragem."
            ]);
            exit;
        }
    }
}