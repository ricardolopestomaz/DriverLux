<?php

require_once __DIR__ . '/../../config/db_connect.php';
require_once __DIR__ . '/../Service/UsuarioService.php';
require_once __DIR__ . '/../Model/UsuarioModel.php';

class UsuarioController {
    private $service;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();

        $model = new UsuarioModel($db);
        $this->service = new UsuarioService($model);
    }

    public function handleRequest($method, $id, $action = null) {
        switch ($method) {
            case 'GET':
                $url = $_SERVER['REQUEST_URI'];
                if (strpos($url, '/me') !== false) {
                    $this->me();
                } elseif ($id) {
                    $this->getUsuario($id);
                } else {
                    $this->getUsuarios();
                }
                break;

            case 'POST':
                $urlAtual = $_SERVER['REQUEST_URI'];

                if ($id === 'login' || $action === 'login' || strpos($urlAtual, '/login') !== false) {
                    $this->login($method);
                } elseif ($id === 'logout' || $action === 'logout' || strpos($urlAtual, '/logout') !== false) {
                    $this->logout();
                } elseif (strpos($urlAtual, '/verificar-email') !== false) {
                    $this->verificarEmail();
                } elseif (strpos($urlAtual, '/atualizar-senha-direta') !== false) {
                    $this->atualizarSenhaDireta();
                } else {
                    $this->createUsuario();
                }
                break;

            case 'PUT':
                $this->updateUsuario($id);
                break;

            default:
                $this->sendResponse([
                    "status_code" => 405,
                    "body" => ["mensagem" => "Método HTTP não permitido."]
                ]);
                break;
        }
    }

    private function getUsuarios() {
        $response = $this->service->listarUsuarios();
        $this->sendResponse($response);
    }

    private function getUsuario($id) {
        $response = $this->service->buscarUsuario($id);
        $this->sendResponse($response);
    }

    private function createUsuario() {
        $data = json_decode(file_get_contents("php://input"));
        $response = $this->service->criarUsuario($data);
        $this->sendResponse($response);
    }

    private function verificarEmail() {
        $data = json_decode(file_get_contents("php://input"));
        $response = $this->service->verificarEmailExistente($data);
        $this->sendResponse($response);
    }

    private function atualizarSenhaDireta() {
        $data = json_decode(file_get_contents("php://input"));
        $response = $this->service->substituirSenhaDireta($data);
        $this->sendResponse($response);
    }

    private function updateUsuario($id) {
        $this->verificarAutenticacao();

        $id_logado = $_SESSION['usuario_id'];
        $perfil_logado = $_SESSION['usuario_perfil'];

        $data = json_decode(file_get_contents("php://input"));
        $response = $this->service->atualizarUsuario($id, $data, $id_logado, $perfil_logado);
        $this->sendResponse($response);
    }

    public function login($method = null) {
        if ($method !== 'POST') {
            $this->sendResponse([
                "status_code" => 405,
                "body" => ["erro" => "Para logar, envie um POST com email e senha."]
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        $response = $this->service->tentarLogin($data);

        if ($response['status_code'] === 200 && isset($response['session_data'])) {
            $usuario = $response['session_data'];

            // 🛑 MODIFICAÇÃO AQUI: Verifica se o usuário retornado está inativo
            // Se na sua Service/Model a coluna 'ativo' vier mapeada, barramos aqui.
            // (Geralmente o MySQL retorna 0 para inativo)
            if (isset($usuario['ativo']) && (int)$usuario['ativo'] === 0) {
                $this->sendResponse([
                    "status_code" => 403,
                    "body" => [
                        "status" => "error", 
                        "erro" => "Sua conta foi desativada pelo administrador."
                    ]
                ]);
                exit;
            }

            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];
            $_SESSION['usuario_nome']   = $usuario['nome'];
            $_SESSION['usuario_email']  = $usuario['email'];
            $_SESSION['usuario_cpf']    = $usuario['cpf'];
            $_SESSION['usuario_foto']   = $usuario['foto_perfil'];
            
            unset($response['session_data']); 
        }

        $this->sendResponse($response);
    }

    private function logout() {
        session_destroy();
        $this->sendResponse([
            "status_code" => 200,
            "body" => ["status" => "success", "mensagem" => "Logout realizado com sucesso."]
        ]);
    }

    private function me() {
        $id_sessao = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
        
        $response = $this->service->obterDadosMe($id_sessao);
        
        // 🛑 SEGUNDA TRAVA DE SEGURANÇA: Se o usuário já estiver logado mas o admin desativou ele
        // a rota '/me' (que roda no JavaScript ao carregar) vai expulsá-lo imediatamente da página.
        if ($response['status_code'] === 200 && isset($response['body']['usuario']['ativo'])) {
            if ((int)$response['body']['usuario']['ativo'] === 0) {
                session_destroy(); // Destrói a sessão atual dele
                $this->sendResponse([
                    "status_code" => 403,
                    "body" => ["logado" => false, "erro" => "Conta desativada."]
                ]);
                exit;
            }
        }

        $this->sendResponse($response);
    }

    private function sendResponse($response) {
        http_response_code($response['status_code']);
        echo json_encode($response['body']);
    }

    private function verificarAutenticacao() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->sendResponse([
                "status_code" => 401,
                "body" => ["status" => "error", "erro" => "Acesso negado. Você precisa fazer login primeiro!"]
            ]);
            exit;
        }
    }
}
?>