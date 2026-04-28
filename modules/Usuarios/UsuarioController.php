<?php

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
                $url = $_SERVER['REQUEST_URI'];
                // Se a URL tiver '/me', ele chama a função da sessão
                if (strpos($url, '/me') !== false) {
                    $this->me();
                } elseif ($id) {
                    $this->getUsuario($id);
                } else {
                    $this->getUsuarios();
                }
                break;
            case 'POST':
                if ($id === 'login' || $action === 'login') {
                    $this->login();
                } elseif ($id === 'logout' || $action === 'logout') {
                    $this->logout();
                } else {
                    $this->createUsuario();
                }
                break;
            case 'PUT':
                $this->updateUsuario($id);
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
        
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($usuarios), "data" => $usuarios]);
    }

    private function getUsuario($id) {
        $query = "SELECT id, nome, cpf, email, perfil, ativo, criado_em FROM usuarios WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $usuario]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "erro" => "Usuário não encontrado."]);
        }
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

    private function updateUsuario($id) {

        $this->verificarAutenticacao();

        // Pega os dados de quem está logando
        $id_logado = $_SESSION['usuario_id'];
        $perfil_logado = $_SESSION['usuario_perfil'];

        // Se o usuário NÃO for 'admin' e o ID != do ID dele mesmo: BLOQUEIA!

        if ($perfil_logado !== 'admin' && $id_logado != $id) {
            http_response_code(403); // 403 = Proibido (Forbidden)
            echo json_encode(["status" => "error", "erro" => "Acesso negado. Você só pode alterar o seu próprio cadastro."]);
            return;
        }

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID do usuário é obrigatório para atualização."]);
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
        if (isset($data->cpf)) {
            $campos[] = "cpf = :cpf";
            $parametros[":cpf"] = $data->cpf;
        }
        if (isset($data->email)) {
            $campos[] = "email = :email";
            $parametros[":email"] = $data->email;
        }
        if (isset($data->perfil)) {
            $campos[] = "perfil = :perfil";
            $parametros[":perfil"] = $data->perfil;
        }
        if (property_exists($data, 'ativo')) {
            $campos[] = "ativo = :ativo";
            $parametros[":ativo"] = $data->ativo;
        }
        // Se a senha for enviada no update, fazemos o hash antes de salvar
        if (isset($data->senha)) {
            $campos[] = "senha_hash = :senha_hash";
            $parametros[":senha_hash"] = password_hash($data->senha, PASSWORD_DEFAULT);
        }

        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum campo válido fornecido para atualização."]);
            return;
        }

        $query = "UPDATE usuarios SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        try {
            if ($stmt->execute($parametros)) {
                if ($stmt->rowCount() > 0) {
                    http_response_code(200);
                    echo json_encode(["status" => "success", "mensagem" => "Usuário atualizado com sucesso."]);
                } else {
                    http_response_code(404);
                    echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
                }
            }
        } catch (PDOException $e) {
            http_response_code(400);
            // Código 23000 = Violação de restrição UNIQUE (CPF ou E-mail já existem)
            if ($e->getCode() == 23000) {
                echo json_encode(["erro" => "CPF ou E-mail já cadastrado por outro usuário."]);
            } else {
                echo json_encode(["erro" => "Erro interno ao atualizar usuário: " . $e->getMessage()]);
            }
        }
    }

    public function login($method = null){
        if ($method !== 'POST') {
            http_response_code(405); // Método não permitido
            echo json_encode(["erro" => "Para logar, envie um POST com email e senha."]);
            return;
        }

        // Lê o JSON enviado
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->email) && !empty($data->senha)) {
            // Busca o usuário no banco pelo e-mail
            $query = "SELECT id, nome, email, senha_hash, perfil FROM usuarios WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":email", $data->email);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verifica se achou o usuário e a senha descriptografada
            if ($usuario && password_verify($data->senha, $usuario['senha_hash'])) {
                
                session_start();

                // Sessao
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];
                $_SESSION['usuario_nome'] = $usuario['nome'];

                http_response_code(200);
                echo json_encode([
                    "status" => "success", 
                    "mensagem" => "Login realizado com sucesso!", 
                    "perfil" => $usuario['perfil']
                ]);
            } else {
                http_response_code(401); // 401 = Não autorizado
                echo json_encode(["status" => "error", "erro" => "E-mail ou senha incorretos."]);
            }
        } else {
            http_response_code(400); // 400 = Requisição ruim
            echo json_encode(["status" => "error", "erro" => "E-mail e senha são obrigatórios."]);
        }
    }

    private function logout() {
        session_start();
        session_destroy();

        http_response_code(200);
        echo json_encode(["status" => "success", "mensagem" => "Logout realizado com sucesso."]);
    }

    // Segurança
    private function verificarAutenticacao() {
        session_start();
        
        // Se a variável de sessão NÃO existir, ele barra!
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error", 
                "erro" => "Acesso negado. Você precisa fazer login primeiro!"
            ]);
            exit; // O 'exit' mata o processo
        }
    }

    private function me() {
        session_start();
        
        if (isset($_SESSION['usuario_id'])) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "logado" => true,
                "usuario" => [
                    "id" => $_SESSION['usuario_id'],
                    "nome" => $_SESSION['usuario_nome'],
                    "perfil" => $_SESSION['usuario_perfil']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                "status" => "error", 
                "logado" => false, 
                "mensagem" => "Nenhum usuário logado no momento."
            ]);
        }
    }

}
?>