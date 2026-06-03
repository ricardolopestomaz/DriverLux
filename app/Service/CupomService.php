<?php

require_once __DIR__ . '/../Model/CupomModel.php';

class CupomService {

    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function listarCupons() {

        $cupons = $this->model->buscarTodos();

        return [
            "status_code" => 200,
            "body" => [
                "status" => "success",
                "total" => count($cupons),
                "data" => $cupons
            ]
        ];
    }

    public function criarCupom($data) {

        if (
            empty($data->codigo) ||
            empty($data->tipo_desconto) ||
            !isset($data->valor_desconto) ||
            empty($data->data_validade)
        ) {

            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "Dados incompletos."
                ]
            ];
        }

        try {

            $resultado = $this->model->inserir($data);

            if ($resultado) {

                return [
                    "status_code" => 201,
                    "body" => [
                        "mensagem" => "Cupom cadastrado com sucesso."
                    ]
                ];
            }

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {

                return [
                    "status_code" => 400,
                    "body" => [
                        "erro" => "Este código de cupom já existe."
                    ]
                ];
            }

            return [
                "status_code" => 500,
                "body" => [
                    "erro" => "Erro ao cadastrar cupom."
                ]
            ];
        }
    }

    public function atualizarCupom($id, $data) {

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
                    "mensagem" => "Cupom atualizado com sucesso."
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

    public function buscarPorCodigo($codigo) {
        if (empty($codigo)) {
            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "Código do cupom é obrigatório."
                ]
            ];
        }

        $cupom = $this->model->buscarPorCodigo($codigo);

        if ($cupom) {
            return [
                "status_code" => 200,
                "body" => [
                    "status" => "success",
                    "data" => $cupom
                ]
            ];
        }

        return [
            "status_code" => 404,
            "body" => [
                "erro" => "Cupom inválido ou expirado."
            ]
        ];
    }
}