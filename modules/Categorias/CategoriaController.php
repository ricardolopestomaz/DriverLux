<?php
// /modules/Categorias/CategoriaController.php

require_once __DIR__ . '/../../config/db_connect.php';

class CategoriaController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function handleRequest($method, $id) {
        if ($method === 'GET') {
            $this->getCategorias();
        } elseif ($method === 'POST') {
            $this->createCategoria();
        } else {
            http_response_code(405);
            echo json_encode(["erro" => "Método HTTP não permitido."]);
        }
    }

    private function getCategorias() {
        $query = "SELECT id, nome, descricao, valor_base_diaria, imagem_ilustrativa, ativo FROM categorias_veiculos";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $categorias = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($categorias), "data" => $categorias]);
    }

    private function createCategoria() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nome) && isset($data->valor_base_diaria)) {
            $query = "INSERT INTO categorias_veiculos (nome, descricao, valor_base_diaria, imagem_ilustrativa) 
                      VALUES (:nome, :descricao, :valor_base_diaria, :imagem_ilustrativa)";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":nome", $data->nome);
            $stmt->bindParam(":valor_base_diaria", $data->valor_base_diaria);
            
            $descricao = isset($data->descricao) ? $data->descricao : null;
            $imagem_ilustrativa = isset($data->imagem_ilustrativa) ? $data->imagem_ilustrativa : null;
            
            $stmt->bindParam(":descricao", $descricao);
            $stmt->bindParam(":imagem_ilustrativa", $imagem_ilustrativa);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["mensagem" => "Categoria cadastrada com sucesso."]);
            } else {
                http_response_code(400);
                echo json_encode(["erro" => "Erro ao cadastrar categoria."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados incompletos. Nome e valor_base_diaria são obrigatórios."]);
        }
    }
}
?>