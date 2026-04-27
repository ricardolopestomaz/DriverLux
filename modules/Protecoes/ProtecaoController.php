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
        } elseif ($method === 'PUT') {
            $this->updateProtecao($id);
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
        // 🔒 Segurança Admin
        $this->verificarAcessoAdmin();

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

    private function updateProtecao($id) {
        // 🔒 Segurança Admin
        $this->verificarAcessoAdmin();

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID da proteção é obrigatório para atualização."]);
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
        if (isset($data->descricao)) {
            $campos[] = "descricao = :descricao";
            $parametros[":descricao"] = $data->descricao;
        }
        if (isset($data->valor_diario)) {
            $campos[] = "valor_diario = :valor_diario";
            $parametros[":valor_diario"] = $data->valor_diario;
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

        $query = "UPDATE pacotes_protecao SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($parametros)) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "mensagem" => "Proteção atualizada com sucesso."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro interno ao atualizar a proteção."]);
        }
    }

    // SEGURANÇA E AUTORIZAÇÃO (Nível Admin)
    private function verificarAcessoAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se está logado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "erro" => "Acesso negado. Faça login primeiro!"]);
            exit;
        }

        // Verifica se é admin
        if ($_SESSION['usuario_perfil'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["status" => "error", "erro" => "Acesso negado. Apenas administradores podem gerenciar pacotes de proteção."]);
            exit;
        }
    }

    

}
?>