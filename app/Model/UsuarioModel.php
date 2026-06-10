<?php

require_once __DIR__ . '/../../config/db_connect.php';
class UsuarioModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findAll() {
        $query = "SELECT id, nome, cpf, email, perfil, ativo, criado_em FROM usuarios";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT id, nome, cpf, email, perfil, ativo, criado_em FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByIdMe($id) {
        $query = "SELECT id, nome, email, cpf, perfil, foto_perfil FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $query = "SELECT id, nome, email, cpf, senha_hash, perfil, foto_perfil FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data, $senha_hash) {
        $query = "INSERT INTO usuarios (nome, cpf, email, senha_hash, perfil) VALUES (:nome, :cpf, :email, :senha_hash, :perfil)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":nome", $data->nome);
        $stmt->bindParam(":cpf", $data->cpf);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":senha_hash", $senha_hash);
        
        $perfil = isset($data->perfil) ? $data->perfil : 'cliente';
        $stmt->bindParam(":perfil", $perfil);

        return $stmt->execute();
    }

    public function update($id, $campos, $parametros) {
        $query = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute($parametros);
        return $stmt->rowCount();
    }

    public function salvarTokenRecuperacao($email, $token, $expiracao) {
        $query = "UPDATE usuarios 
                  SET recuperacao_token = :token, recuperacao_expira_em = :expiracao 
                  WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->bindParam(":expiracao", $expiracao);
        $stmt->bindParam(":email", $email);
        return $stmt->execute();
    }

    public function findByTokenValido($token) {
        $query = "SELECT id FROM usuarios 
                  WHERE recuperacao_token = :token 
                    AND recuperacao_expira_em > NOW() 
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function redefinirSenhaComToken($id, $senha_hash) {
        $query = "UPDATE usuarios 
                  SET senha_hash = :senha_hash, 
                      recuperacao_token = NULL, 
                      recuperacao_expira_em = NULL 
                  WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":senha_hash", $senha_hash);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>