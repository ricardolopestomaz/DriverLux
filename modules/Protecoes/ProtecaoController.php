<?php
// /modules/Protecoes/ProtecaoController.php

require_once __DIR__ . '/../../config/db_connect.php';

class ProtecaoController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function handleRequest($method, $id) {
        if ($method === 'GET') {
            $this->getProtecoes();
        } elseif ($method === 'POST') {
            $this->createProtecao();
        } else {
            http_response_code(405);
            echo json_encode(["erro" => "Método HTTP não permitido."]);
        }
    }

    private function getProtecoes() {
        $query = "SELECT id, nome, descricao, valor_diario, ativo FROM pacotes_protecao";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $protecoes = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($protecoes), "data" => $protecoes]);
    }

    private function createProtecao() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nome) && isset($data->valor_diario)) {
            $query = "INSERT INTO pacotes_protecao (nome, descricao, valor_diario) VALUES (:nome, :descricao, :valor_diario)";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":nome", $data->nome);
            $stmt->bindParam(":valor_diario", $data->valor_diario);
            
            $descricao = isset($data->descricao) ? $data->descricao : null;
            $stmt->bindParam(":descricao", $descricao);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["mensagem" => "Pacote de proteção cadastrado com sucesso."]);
            } else {
                http_response_code(400);
                echo json_encode(["erro" => "Erro ao cadastrar pacote de proteção."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados incompletos. Nome e valor_diario são obrigatórios."]);
        }
    }
}
?>