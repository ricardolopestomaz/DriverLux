<?php

require_once __DIR__ . '/../Model/UsuarioModel.php';

class UsuarioService {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function listarUsuarios() {
        $usuarios = $this->model->findAll();
        return [
            "status_code" => 200,
            "body" => ["status" => "success", "total" => count($usuarios), "data" => $usuarios]
        ];
    }

    public function buscarUsuario($id) {
        $usuario = $this->model->findById($id);
        if ($usuario) {
            return ["status_code" => 200, "body" => ["status" => "success", "data" => $usuario]];
        }
        return ["status_code" => 404, "body" => ["status" => "error", "erro" => "Usuário não encontrado."]];
    }

    public function criarUsuario($data) {
        if (empty($data->nome) || empty($data->cpf) || empty($data->email) || empty($data->senha)) {
            return ["status_code" => 400, "body" => ["erro" => "Dados incompletos. Nome, cpf, email e senha são obrigatórios."]];
        }

        $senha_hash = password_hash($data->senha, PASSWORD_DEFAULT);

        try {
            $this->model->create($data, $senha_hash);
            return ["status_code" => 201, "body" => ["mensagem" => "Usuário criado com sucesso."]];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ["status_code" => 400, "body" => ["erro" => "CPF ou E-mail já cadastrado."]];
            }
            return ["status_code" => 400, "body" => ["erro" => "Erro ao criar usuário: " . $e->getMessage()]];
        }
    }

    public function atualizarUsuario($id, $data, $id_logado, $perfil_logado) {
        if ($perfil_logado !== 'admin' && $id_logado != $id) {
            return ["status_code" => 403, "body" => ["status" => "error", "erro" => "Acesso negado. Você só pode alterar o seu próprio cadastro."]];
        }

        if (empty($id)) {
            return ["status_code" => 400, "body" => ["erro" => "O ID do usuário é obrigatório para atualização."]];
        }

        if (empty((array)$data)) {
            return ["status_code" => 400, "body" => ["erro" => "Nenhum dado enviado para atualização."]];
        }

        $campos = [];
        $parametros = [":id" => $id];

        if (isset($data->nome)) { $campos[] = "nome = :nome"; $parametros[":nome"] = $data->nome; }
        if (isset($data->cpf)) { $campos[] = "cpf = :cpf"; $parametros[":cpf"] = $data->cpf; }
        if (isset($data->email)) { $campos[] = "email = :email"; $parametros[":email"] = $data->email; }
        if (isset($data->perfil)) { $campos[] = "perfil = :perfil"; $parametros[":perfil"] = $data->perfil; }
        if (property_exists($data, 'ativo')) { $campos[] = "ativo = :ativo"; $parametros[":ativo"] = $data->ativo; }
        if (isset($data->senha)) { 
            $campos[] = "senha_hash = :senha_hash"; 
            $parametros[":senha_hash"] = password_hash($data->senha, PASSWORD_DEFAULT); 
        }

        if (empty($campos)) {
            return ["status_code" => 400, "body" => ["erro" => "Nenhum campo válido fornecido para atualização."]];
        }

        try {
            $linhasAfetadas = $this->model->update($id, $campos, $parametros);
            if ($linhasAfetadas > 0) {
                return ["status_code" => 200, "body" => ["status" => "success", "mensagem" => "Usuário atualizado com sucesso."]];
            }
            return ["status_code" => 200, "body" => ["status" => "success", "mensagem" => "Nenhuma alteração feita."]];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ["status_code" => 400, "body" => ["erro" => "CPF ou E-mail já cadastrado por outro usuário."]];
            }
            return ["status_code" => 400, "body" => ["erro" => "Erro interno ao atualizar usuário: " . $e->getMessage()]];
        }
    }

    public function tentarLogin($data) {
        if (empty($data->email) || empty($data->senha)) {
            return ["status_code" => 400, "body" => ["status" => "error", "erro" => "E-mail e senha são obrigatórios."]];
        }

        $usuario = $this->model->findByEmail($data->email);

        if ($usuario && password_verify($data->senha, $usuario['senha_hash'])) {
            return [
                "status_code" => 200,
                "body" => [
                    "status" => "success",
                    "mensagem" => "Login realizado com sucesso!",
                    "perfil" => $usuario['perfil']
                ],
                "session_data" => $usuario 
            ];
        }

        return ["status_code" => 401, "body" => ["status" => "error", "erro" => "E-mail ou senha incorretos."]];
    }

    public function obterDadosMe($id_sessao) {
        if (!$id_sessao) {
            return ["status_code" => 401, "body" => ["status" => "error", "logado" => false, "mensagem" => "Nenhum usuário logado."]];
        }

        $usuario = $this->model->findByIdMe($id_sessao);

        if ($usuario) {
            return [
                "status_code" => 200,
                "body" => ["status" => "success", "logado" => true, "usuario" => $usuario]
            ];
        }

        return ["status_code" => 404, "body" => ["status" => "error", "logado" => false, "mensagem" => "Usuário não encontrado."]];
    }
}
?>