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
        } elseif ($method === 'PUT') {
           $this->updateCupom($id);
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
        // 🔒 Segurança Admin
        $this->verificarAcessoAdmin();

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

    private function updateCupom($id) {
        // 🔒 Segurança Admin
        $this->verificarAcessoAdmin();

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID do cupom é obrigatório para atualização."]);
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

        // Mapeamento corrigido conforme a sua tabela
        if (isset($data->codigo)) {
            $campos[] = "codigo = :codigo";
            $parametros[":codigo"] = $data->codigo;
        }
        if (isset($data->descricao)) {
            $campos[] = "descricao = :descricao";
            $parametros[":descricao"] = $data->descricao;
        }
        if (isset($data->tipo_desconto)) {
            $campos[] = "tipo_desconto = :tipo_desconto";
            $parametros[":tipo_desconto"] = $data->tipo_desconto;
        }
        if (isset($data->valor_desconto)) { // <-- Corrigido aqui!
            $campos[] = "valor_desconto = :valor_desconto";
            $parametros[":valor_desconto"] = $data->valor_desconto;
        }
        if (isset($data->data_inicio)) {
            $campos[] = "data_inicio = :data_inicio";
            $parametros[":data_inicio"] = $data->data_inicio;
        }
        if (isset($data->data_validade)) {
            $campos[] = "data_validade = :data_validade";
            $parametros[":data_validade"] = $data->data_validade;
        }
        if (isset($data->limite_usos)) {
            $campos[] = "limite_usos = :limite_usos";
            $parametros[":limite_usos"] = $data->limite_usos;
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

        // ATENÇÃO: Substitua 'cupons' pelo nome real da sua tabela, se for diferente
        $query = "UPDATE cupons SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($parametros)) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "mensagem" => "Cupom atualizado com sucesso."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro interno ao atualizar o cupom."]);
        }
    }

    // SEGURANÇA E AUTORIZAÇÃO (Nível Admin)
    private function verificarAcessoAdmin() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "erro" => "Acesso negado. Faça login primeiro!"]);
            exit;
        }

        if ($_SESSION['usuario_perfil'] !== 'admin') {
            http_response_code(403);
            echo json_encode(["status" => "error", "erro" => "Acesso negado. Apenas administradores podem gerenciar cupons."]);
            exit;
        }
    }

}
?>