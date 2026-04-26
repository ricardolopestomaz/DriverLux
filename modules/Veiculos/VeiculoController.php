<?php
// /modules/Veiculos/VeiculoController.php

require_once __DIR__ . '/../../config/db_connect.php';

class VeiculoController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function handleRequest($method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getVeiculo($id);
                } else {
                    $this->getVeiculos();
                }
                break;
            case 'POST':
                $this->createVeiculo();
                break;
            default:
                http_response_code(405);
                echo json_encode(["erro" => "Método HTTP não permitido."]);
                break;
        }
    }

    private function getVeiculos() {
        // Traz os dados do carro E da categoria correspondente
        $query = "SELECT 
                    v.id, v.marca, v.modelo, v.ano, v.placa, v.status_disponibilidade, v.imagem_url,
                    c.nome AS categoria_nome, c.valor_base_diaria 
                  FROM veiculos v
                  LEFT JOIN categorias_veiculos c ON v.categoria_id = c.id
                  WHERE v.ativo = TRUE";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $veiculos = $stmt->fetchAll();
        
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "total" => count($veiculos),
            "data" => $veiculos
        ]);
    }

    private function getVeiculo($id) {
        $query = "SELECT 
                    v.id, v.marca, v.modelo, v.ano, v.placa, v.chassi, v.status_disponibilidade, v.imagem_url,
                    c.nome AS categoria_nome, c.valor_base_diaria 
                  FROM veiculos v
                  LEFT JOIN categorias_veiculos c ON v.categoria_id = c.id
                  WHERE v.id = :id AND v.ativo = TRUE";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $veiculo = $stmt->fetch();
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $veiculo]);
        } else {
            http_response_code(404);
            echo json_encode(["erro" => "Veículo não encontrado."]);
        }
    }

    private function createVeiculo() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->categoria_id) && !empty($data->marca) && !empty($data->modelo) && !empty($data->placa)) {
            
            $query = "INSERT INTO veiculos (categoria_id, marca, modelo, ano, placa, chassi) 
                      VALUES (:categoria_id, :marca, :modelo, :ano, :placa, :chassi)";
            
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":categoria_id", $data->categoria_id);
            $stmt->bindParam(":marca", $data->marca);
            $stmt->bindParam(":modelo", $data->modelo);
            $stmt->bindParam(":ano", $data->ano);
            $stmt->bindParam(":placa", $data->placa);
            $stmt->bindParam(":chassi", $data->chassi);

            try {
                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(["mensagem" => "Veículo cadastrado com sucesso."]);
                }
            } catch (PDOException $e) {
                http_response_code(400);
                if ($e->getCode() == 23000) {
                    echo json_encode(["erro" => "Placa ou Chassi já cadastrados."]);
                } else {
                    echo json_encode(["erro" => "Erro ao cadastrar veículo: " . $e->getMessage()]);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados incompletos. Categoria, marca, modelo e placa são obrigatórios."]);
        }
    }
}
?>