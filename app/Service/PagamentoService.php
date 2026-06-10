<?php

require_once __DIR__ . '/../Model/PagamentoModel.php';

class PagamentoService {

    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function listarPagamentos() {

        $pagamentos = $this->model->buscarTodos();

        return [
            "status_code" => 200,
            "body" => [
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
                "status_code" => 200,
                "body" => [
                    "status" => "success",
                    "data" => $pagamento
                ]
            ];
        }

        return [
            "status_code" => 404,
            "body" => [
                "erro" => "Pagamento não encontrado."
            ]
        ];
    }

    public function criarPagamento($data) {

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
                "status_code" => 400,
                "body" => [
                    "erro" => "Dados obrigatórios do pagamento estão faltando."
                ]
            ];
        }

        $tipos_validos = ['credito', 'debito'];

        if (!in_array($data->tipo_cartao, $tipos_validos)) {

            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "Tipo de cartão inválido."
                ]
            ];
        }

        $parcelas = isset($data->parcelas) ? (int)$data->parcelas : 1;

        if ($parcelas < 1 || $parcelas > 12) {

            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "Parcelas devem estar entre 1 e 12."
                ]
            ];
        }

        if (!$this->model->verificarReservaUsuario($data->reserva_id, $usuario_id)) {

            return [
                "status_code" => 403,
                "body" => [
                    "erro" => "Reserva não pertence ao usuário."
                ]
            ];
        }

        if ($this->model->verificarPagamentoDuplicado($data->reserva_id)) {

            return [
                "status_code" => 409,
                "body" => [
                    "erro" => "Reserva já possui pagamento aprovado."
                ]
            ];
        }

        // Mascara o número do cartão antes de salvar no banco por segurança (PCI-DSS)
        $num_limpo = preg_replace('/\D/', '', $data->numero_cartao);
        $ultimos_quatro = substr($num_limpo, -4);
        $data->numero_cartao = '**** **** **** ' . ($ultimos_quatro ?: '0000');

        $resultado = $this->model->inserir($data, $usuario_id, $parcelas);

        if ($resultado) {

            $this->model->confirmarReserva($data->reserva_id);
            $this->model->registrarUsoCupomDaReserva($data->reserva_id);

            return [
                "status_code" => 201,
                "body" => [
                    "status" => "success",
                    "mensagem" => "Pagamento realizado com sucesso!"
                ]
            ];
        }

        return [
            "status_code" => 500,
            "body" => [
                "erro" => "Erro ao processar pagamento."
            ]
        ];
    }

    public function atualizarPagamento($id, $data) {

        if (empty($id)) {

            return [
                "status_code" => 400,
                "body" => [
                    "erro" => "ID obrigatório."
                ]
            ];
        }

        if (empty($data->status)) {

            return [
                "status_code" => 400,
                "body" => [
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
                "status_code" => 400,
                "body" => [
                    "erro" => "Status inválido."
                ]
            ];
        }

        $resultado = $this->model->atualizarStatus($id, $data->status);

        if ($resultado) {

            return [
                "status_code" => 200,
                "body" => [
                    "status" => "success",
                    "mensagem" => "Pagamento atualizado com sucesso."
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