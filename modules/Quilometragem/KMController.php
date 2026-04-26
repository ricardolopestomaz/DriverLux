<?php
// /modules/Quilometragem/KMController.php

require_once __DIR__ . '/../../config/db_connect.php';

class KMController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function handleRequest($method, $id) {
        if ($method === 'GET') {
            $this->getOpcoesKM();
        } elseif ($method === 'POST') {
            $this->createOpcaoKM();
        } else {
            http_response_code(405);
            echo json_encode(["erro" => "Método HTTP não permitido."]);
        }
    }

    private function getOpcoesKM() {
        $query = "SELECT id, nome, limite_km, valor_diario, taxa_km_excedente, ativo FROM opcoes_quilometragem";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $opcoes = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($opcoes), "data" => $opcoes]);
    }

    private function createOpcaoKM() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nome) && isset($data->valor_diario)) {
            $query = "INSERT INTO opcoes_quilometragem (nome, limite_km, valor_diario, taxa_km_excedente) 
                      VALUES (:nome, :limite_km, :valor_diario, :taxa_km_excedente)";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":nome", $data->nome);
            $stmt->bindParam(":valor_diario", $data->valor_diario);
            
            $limite_km = isset($data->limite_km) ? $data->limite_km : null;
            $taxa_km_excedente = isset($data->taxa_km_excedente) ? $data->taxa_km_excedente : null;
            
            $stmt->bindParam(":limite_km", $limite_km);
            $stmt->bindParam(":taxa_km_excedente", $taxa_km_excedente);

            if ($stmt->execute()) {
                http_response_code(201);
                echo json_encode(["mensagem" => "Opção de quilometragem cadastrada com sucesso."]);
            } else {
                http_response_code(400);
                echo json_encode(["erro" => "Erro ao cadastrar opção de KM."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados incompletos. Nome e valor_diario são obrigatórios."]);
        }
    }
}
?>