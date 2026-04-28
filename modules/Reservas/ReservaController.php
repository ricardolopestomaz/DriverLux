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
        } elseif ($method === 'PUT') {
            $this->updateReserva($id);
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
        // 🔒 Chama a segurança
        $this->verificarAutenticacao();
        $data = json_decode(file_get_contents("php://input"));

        // 🔒 Pega o ID do usuário diretamente da Sessão (Garante que ele só reserva para ele mesmo)
        $id_do_usuario_logado = $_SESSION['usuario_id'];

        // Verificação básica dos dados primordiais
        if (!empty($id_do_usuario_logado) && !empty($data->veiculo_id) && !empty($data->data_retirada) && !empty($data->data_devolucao) && isset($data->valor_total_previsto)) {            
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

            // 🔒 Injeta o ID seguro da sessão no banco de dados

            $stmt->bindParam(":usuario_id", $id_do_usuario_logado);
            
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

    private function updateReserva($id) {
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(["erro" => "O ID da reserva é obrigatório para atualização."]);
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

        // Mapeamento exato conforme a imagem da base de dados
        
        // Chaves Estrangeiras (IDs)
        if (isset($data->usuario_id)) { $campos[] = "usuario_id = :usuario_id"; $parametros[":usuario_id"] = $data->usuario_id; }
        if (isset($data->veiculo_id)) { $campos[] = "veiculo_id = :veiculo_id"; $parametros[":veiculo_id"] = $data->veiculo_id; }
        if (property_exists($data, 'cupom_id')) { $campos[] = "cupom_id = :cupom_id"; $parametros[":cupom_id"] = $data->cupom_id; }
        if (property_exists($data, 'opcao_quilometragem_id')) { $campos[] = "opcao_quilometragem_id = :opcao_quilometragem_id"; $parametros[":opcao_quilometragem_id"] = $data->opcao_quilometragem_id; }
        if (property_exists($data, 'pacote_protecao_id')) { $campos[] = "pacote_protecao_id = :pacote_protecao_id"; $parametros[":pacote_protecao_id"] = $data->pacote_protecao_id; }

        // Datas e Locais
        if (isset($data->data_retirada)) { $campos[] = "data_retirada = :data_retirada"; $parametros[":data_retirada"] = $data->data_retirada; }
        if (isset($data->data_devolucao)) { $campos[] = "data_devolucao = :data_devolucao"; $parametros[":data_devolucao"] = $data->data_devolucao; }
        if (isset($data->local_retirada)) { $campos[] = "local_retirada = :local_retirada"; $parametros[":local_retirada"] = $data->local_retirada; }
        if (isset($data->local_devolucao)) { $campos[] = "local_devolucao = :local_devolucao"; $parametros[":local_devolucao"] = $data->local_devolucao; }
        
        // Status e Ativo
        if (isset($data->status)) { $campos[] = "status = :status"; $parametros[":status"] = $data->status; }
        if (property_exists($data, 'ativo')) { $campos[] = "ativo = :ativo"; $parametros[":ativo"] = $data->ativo; }

        // Valores (Decimais)
        if (isset($data->valor_diarias)) { $campos[] = "valor_diarias = :valor_diarias"; $parametros[":valor_diarias"] = $data->valor_diarias; }
        if (isset($data->valor_protecao)) { $campos[] = "valor_protecao = :valor_protecao"; $parametros[":valor_protecao"] = $data->valor_protecao; }
        if (property_exists($data, 'valor_desconto')) { $campos[] = "valor_desconto = :valor_desconto"; $parametros[":valor_desconto"] = $data->valor_desconto; }
        if (property_exists($data, 'taxa_aluguel_percentual')) { $campos[] = "taxa_aluguel_percentual = :taxa_aluguel_percentual"; $parametros[":taxa_aluguel_percentual"] = $data->taxa_aluguel_percentual; }
        if (isset($data->valor_total_previsto)) { $campos[] = "valor_total_previsto = :valor_total_previsto"; $parametros[":valor_total_previsto"] = $data->valor_total_previsto; }

        if (empty($campos)) {
            http_response_code(400);
            echo json_encode(["erro" => "Nenhum campo válido fornecido para atualização."]);
            return;
        }

        $query = "UPDATE reservas SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);

        if ($stmt->execute($parametros)) {
            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(["status" => "success", "mensagem" => "Reserva atualizada com sucesso."]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["erro" => "Erro interno ao atualizar a reserva."]);
        }
    }

    // SEGURANÇA E AUTORIZAÇÃO (Nível Cliente)
    private function verificarAutenticacao() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se existe alguém logado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error", 
                "erro" => "Acesso negado. Você precisa fazer login para realizar uma reserva!"
            ]);
            exit; // Mata o processo e impede a reserva
        }
    }

}
?>