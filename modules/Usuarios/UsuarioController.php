<?php
// /modules/Usuarios/UsuarioController.php

// Usa __DIR__ para garantir o caminho correto independentemente de onde o script é chamado
require_once __DIR__ . '/../../config/db_connect.php';

class UsuarioController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function handleRequest($method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getUsuario($id);
                } else {
                    $this->getUsuarios();
                }
                break;
            case 'POST':
                $this->createUsuario();
                break;
            default:
                http_response_code(405);
                echo json_encode(["mensagem" => "Método HTTP não permitido."]);
                break;
        }
    }

    private function getUsuarios() {
        $query = "SELECT id, nome, cpf, email, perfil, ativo, criado_em FROM usuarios";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $usuarios = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode($usuarios);
    }

    private function createUsuario() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->nome) && !empty($data->cpf) && !empty($data->email) && !empty($data->senha)) {
            $senha_hash = password_hash($data->senha, PASSWORD_DEFAULT);

            $query = "INSERT INTO usuarios (nome, cpf, email, senha_hash, perfil) VALUES (:nome, :cpf, :email, :senha_hash, :perfil)";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":nome", $data->nome);
            $stmt->bindParam(":cpf", $data->cpf);
            $stmt->bindParam(":email", $data->email);
            $stmt->bindParam(":senha_hash", $senha_hash);
            
            $perfil = isset($data->perfil) ? $data->perfil : 'cliente';
            $stmt->bindParam(":perfil", $perfil);

            try {
                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(["mensagem" => "Usuário criado com sucesso."]);
                }
            } catch (PDOException $e) {
                http_response_code(400);
                if ($e->getCode() == 23000) {
                    echo json_encode(["erro" => "CPF ou E-mail já cadastrado."]);
                } else {
                    echo json_encode(["erro" => "Erro ao criar usuário: " . $e->getMessage()]);
                }
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados incompletos. Nome, cpf, email e senha são obrigatórios."]);
        }
    }
}
?>