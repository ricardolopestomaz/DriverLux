<?php
// /modules/Pagamentos/PagamentoController.php

require_once __DIR__ . '/../../config/db_connect.php';

class PagamentoController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function handleRequest($method, $id) {
        if ($method === 'GET') {
            if ($id) {
                $this->getPagamento($id);
            } else {
                $this->getPagamentos();
            }
        } elseif ($method === 'POST') {
            $this->createPagamento();
        } elseif ($method === 'PUT') {
            $this->updatePagamento($id);
        } else {
            http_response_code(405);
            echo json_encode(["erro" => "Método HTTP não permitido."]);
        }
    }

    private function getPagamentos() {
        $query = "SELECT p.id, p.tipo_cartao, p.valor_total, p.parcelas, p.status,
                         u.nome AS cliente, r.id AS reserva_id,
                         r.data_retirada, r.data_devolucao,
                         p.criado_em
                  FROM pagamentos p
                  JOIN usuarios u ON p.usuario_id = u.id
                  JOIN reservas r ON p.reserva_id = r.id
                  ORDER BY p.criado_em DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $pagamentos = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($pagamentos), "data" => $pagamentos]);
    }

    private function getPagamento($id) {
        $query = "SELECT * FROM pagamentos WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $stmt->fetch()]);
        } else {
            http_response_code(404);
            echo json_encode(["erro" => "Pagamento não encontrado."]);
        }
    }

    private function createPagamento() {
        
        $this->verificarAutenticacao();
        $data = json_decode(file_get_contents("php://input"));

        
        $id_do_usuario_logado = $_SESSION['usuario_id'];

        // Campos obrigatórios
        if (
            !empty($data->reserva_id) &&
            !empty($data->tipo_cartao) &&
            !empty($data->valor_total) &&
            !empty($data->nome_titular) &&
            !empty($data->numero_cartao) &&
            !empty($data->cvv) &&
            !empty($data->mes_vencimento) &&
            !empty($data->ano_vencimento)
        ) {
            // 🔒 Valida tipo do cartão
            $tipos_validos = ['credito', 'debito'];
            if (!in_array($data->tipo_cartao, $tipos_validos)) {
                http_response_code(400);
                echo json_encode(["erro" => "Tipo de cartão inválido. Use 'credito' ou 'debito'."]);
                return;
            }

            $parcelas = isset($data->parcelas) ? (int)$data->parcelas : 1;

            // 🔒 Parcelas entre 1 e 12
            if ($parcelas < 1 || $parcelas > 12) {
                http_response_code(400);
                echo json_encode(["erro" => "O número de parcelas deve ser entre 1 e 12."]);
                return;
            }

            // 🔒 Verifica se a reserva pertence ao usuário logado
            $this->verificarDonoDaReserva($data->reserva_id, $id_do_usuario_logado);

            // 🔒 Verifica se a reserva já possui pagamento aprovado
            $this->verificarPagamentoDuplicado($data->reserva_id);

            $query = "INSERT INTO pagamentos (
                        reserva_id, usuario_id, tipo_cartao, valor_total, parcelas,
                        nome_titular, numero_cartao, cvv, mes_vencimento, ano_vencimento
                      ) VALUES (
                        :reserva_id, :usuario_id, :tipo_cartao, :valor_total, :parcelas,
                        :nome_titular, :numero_cartao, :cvv, :mes_vencimento, :ano_vencimento
                      )";

            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":reserva_id",     $data->reserva_id);
            $stmt->bindParam(":usuario_id",     $id_do_usuario_logado);
            $stmt->bindParam(":tipo_cartao",    $data->tipo_cartao);
            $stmt->bindParam(":valor_total",    $data->valor_total);
            $stmt->bindParam(":parcelas",       $parcelas);
            $stmt->bindParam(":nome_titular",   $data->nome_titular);
            $stmt->bindParam(":numero_cartao",  $data->numero_cartao);
            $stmt->bindParam(":cvv",            $data->cvv);
            $stmt->bindParam(":mes_vencimento", $data->mes_vencimento);
            $stmt->bindParam(":ano_vencimento", $data->ano_vencimento);

            if ($stmt->execute()) {
                $this->confirmarReserva($data->reserva_id);

                http_response_code(201);
                echo json_encode(["status" => "success", "mensagem" => "Pagamento realizado com sucesso!"]);
            } else {
                http_response_code(500);
                echo json_encode(["erro" => "Erro ao processar o pagamento."]);
            }

        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados obrigatórios do pagamento estão faltando."]);
        }
    }

    private function updatePagamento($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID do pagamento é obrigatório para atualização."]);
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

        if (isset($data->status)) {
            $status_validos = ['pendente', 'aprovado', 'recusado', 'estornado'];
            if (!in_array($data->status, $status_validos)) {
                http_response_code(400);
                echo json_encode(["erro" => "Status inválido."]);
                return;
            }
            $campos[] = "status = :status";
            $parametros[":status"] = $data->status;
        }

        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum campo válido fornecido para atualização."]);
            return;
        }

        $query = "UPDATE pagamentos SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt  = $this->db->prepare($query);

        if ($stmt->execute($parametros)) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "mensagem" => "Pagamento atualizado com sucesso."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro interno ao atualizar o pagamento."]);
        }
    }

    private function confirmarReserva($reserva_id) {
        $query = "UPDATE reservas SET status = 'confirmada' WHERE id = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(":id", $reserva_id);
        $stmt->execute();
    }

    private function verificarDonoDaReserva($reserva_id, $usuario_id) {
        $query = "SELECT id FROM reservas WHERE id = :reserva_id AND usuario_id = :usuario_id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(":reserva_id", $reserva_id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo json_encode(["erro" => "Acesso negado. Esta reserva não pertence ao usuário logado."]);
            exit;
        }
    }

    private function verificarPagamentoDuplicado($reserva_id) {
        $query = "SELECT id FROM pagamentos WHERE reserva_id = :reserva_id AND status = 'aprovado'";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(":reserva_id", $reserva_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(["erro" => "Esta reserva já possui um pagamento aprovado."]);
            exit;
        }
    }

    private function verificarAutenticacao() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "erro"   => "Acesso negado. Você precisa fazer login para realizar um pagamento!"
            ]);
            exit;
        }
    }
}
?>