<?php
// /modules/Categorias/CategoriaController.php

require_once __DIR__ . '/../../config/db_connect.php';

class CategoriaController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function handleRequest($method, $id = null) {
        if ($method === 'GET') {
            $this->getCategorias();
        } elseif ($method === 'POST') {
            $this->createCategoria();
        } elseif ($method === 'PUT') {
            $this->updateCategoria($id);
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
        $this->verificarAcessoAdmin(); // 🔒 Segurança Admin

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

    private function updateCategoria($id) {
        $this->verificarAcessoAdmin(); // 🔒 Segurança Admin

        // Verifica se o ID foi passado
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID da categoria é obrigatório para atualização."]);
            return;
        }

        // Captura os dados do corpo da requisição
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum dado enviado para atualização."]);
            return;
        }

        // Construtor Dinâmico de Query
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
        if (isset($data->valor_base_diaria)) {
            $campos[] = "valor_base_diaria = :valor_base_diaria";
            $parametros[":valor_base_diaria"] = $data->valor_base_diaria;
        }
        if (isset($data->imagem_ilustrativa)) {
            $campos[] = "imagem_ilustrativa = :imagem_ilustrativa";
            $parametros[":imagem_ilustrativa"] = $data->imagem_ilustrativa;
        }
        if (isset($data->ativo)) {
            $campos[] = "ativo = :ativo";
            $parametros[":ativo"] = $data->ativo;
        }

        // Se nenhum campo válido foi enviado
        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum campo válido fornecido para atualização."]);
            return;
        }

        // Monta e executa a SQL
        $query = "UPDATE categorias_veiculos SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($parametros)) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "mensagem" => "Categoria atualizada com sucesso."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro interno ao atualizar a categoria."]);
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
            echo json_encode(["status" => "error", "erro" => "Acesso negado. Apenas administradores podem gerenciar categorias."]);
            exit;
        }
    }

}
?>