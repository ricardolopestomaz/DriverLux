<?php
// /modules/Reservas/ReservaController.php

require_once __DIR__ . '/../../config/db_connect.php';

class ReservaController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function handleRequest($method, $id) {
        if ($method === 'GET') {
            if ($id) {
                $this->getReserva($id);
            } else {
                $this->getReservas();
            }
        } elseif ($method === 'POST') {
            $this->createReserva();
        } else {
            http_response_code(405);
            echo json_encode(["erro" => "Método HTTP não permitido."]);
        }
    }

    private function getReservas() {
        // Traz informações chave usando JOIN para ficar legível para o Frontend
        $query = "SELECT r.id, r.data_retirada, r.data_devolucao, r.valor_total_previsto, r.status,
                         u.nome AS cliente, v.modelo AS veiculo
                  FROM reservas r
                  JOIN usuarios u ON r.usuario_id = u.id
                  JOIN veiculos v ON r.veiculo_id = v.id
                  ORDER BY r.criado_em DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $reservas = $stmt->fetchAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "total" => count($reservas), "data" => $reservas]);
    }

    private function getReserva($id) {
        $query = "SELECT * FROM reservas WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $stmt->fetch()]);
        } else {
            http_response_code(404);
            echo json_encode(["erro" => "Reserva não encontrada."]);
        }
    }

    private function createReserva() {
        $data = json_decode(file_get_contents("php://input"));

        // Verificação básica dos dados primordiais
        if (!empty($data->usuario_id) && !empty($data->veiculo_id) && !empty($data->data_retirada) && !empty($data->data_devolucao) && isset($data->valor_total_previsto)) {
            
            $query = "INSERT INTO reservas (
                        usuario_id, veiculo_id, pacote_protecao_id, opcao_quilometragem_id, cupom_id,
                        local_retirada, local_devolucao, data_retirada, data_devolucao,
                        valor_diarias, valor_protecao, taxa_aluguel_percentual, valor_desconto, valor_total_previsto
                      ) VALUES (
                        :usuario_id, :veiculo_id, :pacote_protecao_id, :opcao_quilometragem_id, :cupom_id,
                        :local_retirada, :local_devolucao, :data_retirada, :data_devolucao,
                        :valor_diarias, :valor_protecao, :taxa_aluguel_percentual, :valor_desconto, :valor_total_previsto
                      )";
            
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":usuario_id", $data->usuario_id);
            $stmt->bindParam(":veiculo_id", $data->veiculo_id);
            $stmt->bindParam(":pacote_protecao_id", $data->pacote_protecao_id);
            $stmt->bindParam(":opcao_quilometragem_id", $data->opcao_quilometragem_id);
            
            $cupom_id = isset($data->cupom_id) ? $data->cupom_id : null;
            $stmt->bindParam(":cupom_id", $cupom_id);

            $stmt->bindParam(":local_retirada", $data->local_retirada);
            $stmt->bindParam(":local_devolucao", $data->local_devolucao);
            $stmt->bindParam(":data_retirada", $data->data_retirada);
            $stmt->bindParam(":data_devolucao", $data->data_devolucao);
            
            $stmt->bindParam(":valor_diarias", $data->valor_diarias);
            $stmt->bindParam(":valor_protecao", $data->valor_protecao);
            
            $taxa = isset($data->taxa_aluguel_percentual) ? $data->taxa_aluguel_percentual : 15.00;
            $stmt->bindParam(":taxa_aluguel_percentual", $taxa);
            
            $desconto = isset($data->valor_desconto) ? $data->valor_desconto : 0.00;
            $stmt->bindParam(":valor_desconto", $desconto);
            
            $stmt->bindParam(":valor_total_previsto", $data->valor_total_previsto);

            if ($stmt->execute()) {
                // Ao criar uma reserva, o ideal seria fazer um UPDATE no veículo mudando o status para 'alugado'.
                http_response_code(201);
                echo json_encode(["mensagem" => "Reserva criada com sucesso!"]);
            } else {
                http_response_code(400);
                echo json_encode(["erro" => "Erro ao criar reserva."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["erro" => "Dados essenciais da reserva estão faltando."]);
        }
    }
}
?>