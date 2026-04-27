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
            case 'PUT':
                $this->updateVeiculo($id);
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

    private function updateVeiculo($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID do veículo é obrigatório para atualização."]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum dado enviado para atualização."]);
            return;
        }

        $campos = [];
        $parametros = [":id" => $id];

        if (isset($data->categoria_id)) { $campos[] = "categoria_id = :categoria_id"; $parametros[":categoria_id"] = $data->categoria_id; }
        if (isset($data->marca)) { $campos[] = "marca = :marca"; $parametros[":marca"] = $data->marca; }
        if (isset($data->modelo)) { $campos[] = "modelo = :modelo"; $parametros[":modelo"] = $data->modelo; }
        if (isset($data->ano)) { $campos[] = "ano = :ano"; $parametros[":ano"] = $data->ano; }
        if (isset($data->placa)) { $campos[] = "placa = :placa"; $parametros[":placa"] = $data->placa; }
        if (isset($data->chassi)) { $campos[] = "chassi = :chassi"; $parametros[":chassi"] = $data->chassi; }
        if (isset($data->status_disponibilidade)) { $campos[] = "status_disponibilidade = :status_disponibilidade"; $parametros[":status_disponibilidade"] = $data->status_disponibilidade; }
        if (isset($data->imagem_url)) { $campos[] = "imagem_url = :imagem_url"; $parametros[":imagem_url"] = $data->imagem_url; }
        if (property_exists($data, 'ativo')) { $campos[] = "ativo = :ativo"; $parametros[":ativo"] = $data->ativo; }

        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum campo válido fornecido para atualização."]);
            return;
        }

        $query = "UPDATE veiculos SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        try {
            if ($stmt->execute($parametros)) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "mensagem" => "Veículo atualizado com sucesso."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
                }
            }
        } catch (PDOException $e) {
            http_response_code(400);
            // Código 23000 = Violação de restrição UNIQUE (Placa ou Chassi já existem)
            if ($e->getCode() == 23000) {
                echo json_encode(["erro" => "Placa ou Chassi já cadastrados em outro veículo."]);
            } else {
                echo json_encode(["erro" => "Erro interno ao atualizar veículo: " . $e->getMessage()]);
            }
        }
    }

}
?>