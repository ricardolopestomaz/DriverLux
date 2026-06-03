<?php

require_once __DIR__ . '/../Model/CategoriaModel.php';

class CategoriaService {

    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function listarCategorias() {

        $categorias = $this->model->buscarTodas();

        return [
            "status_code" => 200,
            "body" => [
                "status" => "success",
                "total" => count($categorias),
                "data" => $categorias
            ]
        ];
    }

    public function criarCategoria($data) {

        if (empty($data->nome) || !isset($data->valor_base_diaria)) {
            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "Dados incompletos."
                ]
            ];
        }

        $resultado = $this->model->inserir($data);

        if ($resultado) {
            return [
                "status_code" => 201,
                "body" => [
                    "mensagem" => "Categoria cadastrada com sucesso."
                ]
            ];
        }

        return [
            "status_code" => 500,
            "body" => [
                "erro" => "Erro ao cadastrar categoria."
            ]
        ];
    }

    public function atualizarCategoria($id, $data) {

        if (empty($id)) {
            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "ID obrigatório."
                ]
            ];
        }

        $resultado = $this->model->atualizar($id, $data);

        if ($resultado["success"]) {
            return [
                "status_code" => 200,
                "body" => [
                    "status" => "success",
                    "mensagem" => "Categoria atualizada com sucesso."
                ]
            ];
        }

        return [
            "status_code" => 404,
            "body" => [
                "erro" => "Nenhuma alteração encontrada."
            ]
        ];
    }
}