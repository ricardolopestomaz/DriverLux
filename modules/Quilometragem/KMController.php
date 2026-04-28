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
        } elseif ($method === 'PUT') {
            $this->updateOpcaoKM($id);
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
        // 🔒 Chama a segurança de Admin
        $this->verificarAcessoAdmin();

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

    private function updateOpcaoKM($id) {
        // 🔒 Chama a segurança de Admin
        $this->verificarAcessoAdmin();

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID da opção de KM é obrigatório para atualização."]);
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

        if (isset($data->nome)) {
            $campos[] = "nome = :nome";
            $parametros[":nome"] = $data->nome;
        }
        
        // Usamos property_exists para aceitar o envio de 'null' no JSON
        if (property_exists($data, 'limite_km')) {
            $campos[] = "limite_km = :limite_km";
            $parametros[":limite_km"] = $data->limite_km;
        }
        
        if (isset($data->valor_diario)) {
            $campos[] = "valor_diario = :valor_diario";
            $parametros[":valor_diario"] = $data->valor_diario;
        }
        
        // O mesmo vale para a taxa extra, permitindo zerar (null)
        if (property_exists($data, 'taxa_km_excedente')) {
            $campos[] = "taxa_km_excedente = :taxa_km_excedente";
            $parametros[":taxa_km_excedente"] = $data->taxa_km_excedente;
        }
        
        if (isset($data->ativo)) {
            $campos[] = "ativo = :ativo";
            $parametros[":ativo"] = $data->ativo;
        }

        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum campo válido fornecido para atualização."]);
            return;
        }

        $query = "UPDATE opcoes_quilometragem SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($parametros)) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "mensagem" => "Opção de KM atualizada com sucesso."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro interno ao atualizar a opção de KM."]);
        }
    }


    // SEGURANÇA E AUTORIZAÇÃO (Nível Admin)
    private function verificarAcessoAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se tem ALGUÉM logado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error", 
                "erro" => "Acesso negado. Você precisa fazer login primeiro!"
            ]);
            exit;
        }

        // Verifica se é ADMIN
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
?>