<?php

require_once __DIR__ . '/../Model/CupomModel.php';

class CupomService {

    private $model;

    public function __construct() {
        $this->model = new CupomModel();
    }

    public function listarCupons() {

        $cupons = $this->model->buscarTodos();

        return [
            "code" => 200,
            "data" => [
                "status" => "success",
                "total" => count($cupons),
                "data" => $cupons
            ]
        ];
    }

    public function criarCupom($data) {

        $this->verificarAcessoAdmin();

        if (
            empty($data->codigo) ||
            empty($data->tipo_desconto) ||
            !isset($data->valor_desconto) ||
            empty($data->data_validade)
        ) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Dados incompletos."
                ]
            ];
        }

        try {

            $resultado = $this->model->inserir($data);

            if ($resultado) {

                return [
                    "code" => 201,
                    "data" => [
                        "mensagem" => "Cupom cadastrado com sucesso."
                    ]
                ];
            }

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {

                return [
                    "code" => 400,
                    "data" => [
                        "erro" => "Este código de cupom já existe."
                    ]
                ];
            }

            return [
                "code" => 500,
                "data" => [
                    "erro" => "Erro ao cadastrar cupom."
                ]
            ];
        }
    }

    public function atualizarCupom($id, $data) {

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
                    "mensagem" => "Cupom atualizado com sucesso."
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