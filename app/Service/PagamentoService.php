<?php

require_once __DIR__ . '/../Model/PagamentoModel.php';

class PagamentoService {

    private $model;

    public function __construct() {
        $this->model = new PagamentoModel();
    }

    public function listarPagamentos() {

        $pagamentos = $this->model->buscarTodos();

        return [
            "code" => 200,
            "data" => [
                "status" => "success",
                "total" => count($pagamentos),
                "data" => $pagamentos
            ]
        ];
    }

    public function buscarPagamento($id) {

        $pagamento = $this->model->buscarPorId($id);

        if ($pagamento) {

            return [
                "code" => 200,
                "data" => [
                    "status" => "success",
                    "data" => $pagamento
                ]
            ];
        }

        return [
            "code" => 404,
            "data" => [
                "erro" => "Pagamento não encontrado."
            ]
        ];
    }

    public function criarPagamento($data) {

        $this->verificarAutenticacao();

        $usuario_id = $_SESSION['usuario_id'];

        if (
            empty($data->reserva_id) ||
            empty($data->tipo_cartao) ||
            empty($data->valor_total) ||
            empty($data->nome_titular) ||
            empty($data->numero_cartao) ||
            empty($data->cvv) ||
            empty($data->mes_vencimento) ||
            empty($data->ano_vencimento)
        ) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Dados obrigatórios do pagamento estão faltando."
                ]
            ];
        }

        $tipos_validos = ['credito', 'debito'];

        if (!in_array($data->tipo_cartao, $tipos_validos)) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Tipo de cartão inválido."
                ]
            ];
        }

        $parcelas = isset($data->parcelas) ? (int)$data->parcelas : 1;

        if ($parcelas < 1 || $parcelas > 12) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Parcelas devem estar entre 1 e 12."
                ]
            ];
        }

        if (!$this->model->verificarReservaUsuario($data->reserva_id, $usuario_id)) {

            return [
                "code" => 403,
                "data" => [
                    "erro" => "Reserva não pertence ao usuário."
                ]
            ];
        }

        if ($this->model->verificarPagamentoDuplicado($data->reserva_id)) {

            return [
                "code" => 409,
                "data" => [
                    "erro" => "Reserva já possui pagamento aprovado."
                ]
            ];
        }

        $resultado = $this->model->inserir($data, $usuario_id, $parcelas);

        if ($resultado) {

            $this->model->confirmarReserva($data->reserva_id);

            return [
                "code" => 201,
                "data" => [
                    "status" => "success",
                    "mensagem" => "Pagamento realizado com sucesso!"
                ]
            ];
        }

        return [
            "code" => 500,
            "data" => [
                "erro" => "Erro ao processar pagamento."
            ]
        ];
    }

    public function atualizarPagamento($id, $data) {

        if (empty($id)) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "ID obrigatório."
                ]
            ];
        }

        if (empty($data->status)) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Status obrigatório."
                ]
            ];
        }

        $status_validos = [
            'pendente',
            'aprovado',
            'recusado',
            'estornado'
        ];

        if (!in_array($data->status, $status_validos)) {

            return [
                "code" => 400,
                "data" => [
                    "erro" => "Status inválido."
                ]
            ];
        }

        $resultado = $this->model->atualizarStatus($id, $data->status);

        if ($resultado) {

            return [
                "code" => 200,
                "data" => [
                    "status" => "success",
                    "mensagem" => "Pagamento atualizado com sucesso."
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

    private function verificarAutenticacao() {

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
    }
}