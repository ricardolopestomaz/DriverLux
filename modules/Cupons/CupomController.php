<?php
// /modules/Cupons/CupomController.php

require_once __DIR__ . '/../../config/db_connect.php';

class CupomController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function handleRequest($method, $id) {
        if ($method === 'GET') {
            $this->getCupons();
        } elseif ($method === 'POST') {
            $this->createCupom();
        } else {
            http_response_code(405);
            echo json_encode(["erro" => "Método HTTP não permitido."]);
        }
    }

    private function getCupons() {
        $query = "SELECT * FROM cupons WHERE ativo = TRUE";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $cupons = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($cupons), "data" => $cupons]);
    }

    private function createCupom() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->codigo) && !empty($data->tipo_desconto) && isset($data->valor_desconto) && !empty($data->data_validade)) {
            $query = "INSERT INTO cupons (codigo, descricao, tipo_desconto, valor_desconto, data_validade, limite_usos) 
                      VALUES (:codigo, :descricao, :tipo_desconto, :valor_desconto, :data_validade, :limite_usos)";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":codigo", $data->codigo);
            $stmt->bindParam(":tipo_desconto", $data->tipo_desconto);
            $stmt->bindParam(":valor_desconto", $data->valor_desconto);
            $stmt->bindParam(":data_validade", $data->data_validade);
            
            $descricao = isset($data->descricao) ? $data->descricao : null;
            $limite_usos = isset($data->limite_usos) ? $data->limite_usos : null;
            
            $stmt->bindParam(":descricao", $descricao);
            $stmt->bindParam(":limite_usos", $limite_usos);

            try {
                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(["mensagem" => "Cupom cadastrado com sucesso."]);
                }
            } catch (PDOException $e) {
                http_response_code(400);
                if ($e->getCode() == 23000) {
                    echo json_encode(["erro" => "Este código de cupom já existe."]);
                } else {
                    echo json_encode(["erro" => "Erro ao cadastrar cupom: " . $e->getMessage()]);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados incompletos. Código, tipo, valor e validade são obrigatórios."]);
        }
    }
}
?>