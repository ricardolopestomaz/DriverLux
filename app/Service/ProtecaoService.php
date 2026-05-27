<?php

require_once __DIR__ . '/../Model/ProtecaoModel.php';

class ProtecaoService {

    private $model;

    public function __construct() {
        $this->model = new ProtecaoModel();
    }

    public function listarProtecoes() {

        $protecoes = $this->model->buscarTodas();

        return [
            "code" => 200,
            "data" => [
                "status" => "success",
                "total" => count($protecoes),
                "data" => $protecoes
            ]
        ];
    }

    public function criarProtecao($data) {

        $this->verificarAcessoAdmin();

        if (
            empty($data->nome) ||
            !isset($data->valor_diario)
        ) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Dados incompletos."
                ]
            ];
        }

        $resultado = $this->model->inserir($data);

        if ($resultado) {

            return [
                "code" => 201,
                "data" => [
                    "mensagem" => "Pacote de proteção cadastrado com sucesso."
                ]
            ];
        }

        return [
            "code" => 500,
            "data" => [
                "erro" => "Erro ao cadastrar pacote de proteção."
            ]
        ];
    }

    public function atualizarProtecao($id, $data) {

        $this->verificarAcessoAdmin();

        if (empty($id)) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "ID obrigatório."
                ]
            ];
        }

        if (empty($data)) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Nenhum dado enviado."
                ]
            ];
        }

        $resultado = $this->model->atualizar($id, $data);

        if ($resultado["success"]) {

            return [
                "code" => 200,
                "data" => [
                    "status" => "success",
                    "mensagem" => "Proteção atualizada com sucesso."
                ]
            ];
        }

        return [
            "code" => 404,
            "data" => [
                "status" => "warning",
                "mensagem" => "Nenhuma alteração feita."
            ]
        ];
    }

    private function verificarAcessoAdmin() {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {

            http_response_code(401);

            echo json_encode([
                "erro" => "Faça login primeiro."
            ]);

            exit;
        }

        if ($_SESSION['usuario_perfil'] !== 'admin') {

            http_response_code(403);

            echo json_encode([
                "erro" => "Apenas administradores."
            ]);

            exit;
        }
    }
}