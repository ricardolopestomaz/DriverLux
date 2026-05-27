<?php

require_once __DIR__ . '/../Model/CategoriaModel.php';

class CategoriaService {

    private $model;

    public function __construct() {
        $this->model = new CategoriaModel();
    }

    public function listarCategorias() {

        $categorias = $this->model->buscarTodas();

        return [
            "code" => 200,
            "data" => [
                "status" => "success",
                "total" => count($categorias),
                "data" => $categorias
            ]
        ];
    }

    public function criarCategoria($data) {

        $this->verificarAcessoAdmin();

        if (empty($data->nome) || !isset($data->valor_base_diaria)) {
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
                    "mensagem" => "Categoria cadastrada com sucesso."
                ]
            ];
        }

        return [
            "code" => 500,
            "data" => [
                "erro" => "Erro ao cadastrar categoria."
            ]
        ];
    }

    public function atualizarCategoria($id, $data) {

        $this->verificarAcessoAdmin();

        if (empty($id)) {
            return [
                "code" => 400,
                "data" => [
                    "erro" => "ID obrigatório."
                ]
            ];
        }

        $resultado = $this->model->atualizar($id, $data);

        if ($resultado["success"]) {
            return [
                "code" => 200,
                "data" => [
                    "status" => "success",
                    "mensagem" => "Categoria atualizada com sucesso."
                ]
            ];
        }

        return [
            "code" => 404,
            "data" => [
                "erro" => "Nenhuma alteração encontrada."
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