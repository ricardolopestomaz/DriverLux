<?php

require_once __DIR__ . '/../Model/ProtecaoModel.php';

class ProtecaoService {

    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function listarProtecoes() {

        $protecoes = $this->model->buscarTodas();

        return [
            "status_code" => 200,
            "body" => [
                "status" => "success",
                "total" => count($protecoes),
                "data" => $protecoes
            ]
        ];
    }

    public function criarProtecao($data) {

        if (
            empty($data->nome) ||
            !isset($data->valor_diario)
        ) {

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
                    "mensagem" => "Pacote de proteção cadastrado com sucesso."
                ]
            ];
        }

        return [
            "status_code" => 500,
            "body" => [
                "erro" => "Erro ao cadastrar pacote de proteção."
            ]
        ];
    }

    public function atualizarProtecao($id, $data) {

        if (empty($id)) {

            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "ID obrigatório."
                ]
            ];
        }

        if (empty($data)) {

            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "Nenhum dado enviado."
                ]
            ];
        }

        $resultado = $this->model->atualizar($id, $data);

        if ($resultado["success"]) {

            return [
                "status_code" => 200,
                "body" => [
                    "status" => "success",
                    "mensagem" => "Proteção atualizada com sucesso."
                ]
            ];
        }

        return [
            "status_code" => 404,
            "body" => [
                "status" => "warning",
                "mensagem" => "Nenhuma alteração feita."
            ]
        ];
    }
}