<?php

require_once __DIR__ . '/../Model/ReservaModel.php';

class ReservaService {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function listarReservas() {
        $reservas = $this->model->findAll();
        return [
            "status_code" => 200, 
            "body" => ["status" => "success", "total" => count($reservas), "data" => $reservas]
        ];
    }

    public function listarReservasPorUsuario($usuario_id) {
        if (empty($usuario_id)) {
            return ["status_code" => 401, "body" => ["erro" => "Usuário não autenticado."]];
        }
        $reservas = $this->model->findByUserId($usuario_id);
        return [
            "status_code" => 200,
            "body" => ["status" => "success", "total" => count($reservas), "data" => $reservas]
        ];
    }

    public function buscarReserva($id) {
        $reserva = $this->model->findById($id);
        if ($reserva) {
            return ["status_code" => 200, "body" => ["status" => "success", "data" => $reserva]];
        }
        return ["status_code" => 404, "body" => ["erro" => "Reserva não encontrada."]];
    }

    public function criarReserva($data, $usuario_id) {
        if (empty($usuario_id) || empty($data->veiculo_id) || empty($data->data_retirada) || empty($data->data_devolucao) || !isset($data->valor_total_previsto)) {
            return ["status_code" => 400, "body" => ["erro" => "Dados essenciais da reserva estão faltando."]];
        }

        try {
            $reservaId = $this->model->create($data, $usuario_id);
            if ($reservaId) {
                return ["status_code" => 201, "body" => ["status" => "success", "mensagem" => "Reserva criada com sucesso!", "id" => $reservaId]];
            }
            return ["status_code" => 400, "body" => ["erro" => "Erro ao gravar reserva no banco de dados."]];
        } catch (PDOException $e) {
            return ["status_code" => 400, "body" => ["erro" => "Erro ao criar reserva: " . $e->getMessage()]];
        }
    }

    public function atualizarReserva($id, $data) {
        if (empty($id)) {
            return ["status_code" => 400, "body" => ["erro" => "O ID da reserva é obrigatório para atualização."]];
        }

        if (empty((array)$data)) {
            return ["status_code" => 400, "body" => ["erro" => "Nenhum dado enviado para atualização."]];
        }

        $campos = [];
        $parametros = [":id" => $id];

        if (isset($data->usuario_id)) { $campos[] = "usuario_id = :usuario_id"; $parametros[":usuario_id"] = $data->usuario_id; }
        if (isset($data->veiculo_id)) { $campos[] = "veiculo_id = :veiculo_id"; $parametros[":veiculo_id"] = $data->veiculo_id; }
        if (property_exists($data, 'cupom_id')) { $campos[] = "cupom_id = :cupom_id"; $parametros[":cupom_id"] = $data->cupom_id; }
        if (property_exists($data, 'opcao_quilometragem_id')) { $campos[] = "opcao_quilometragem_id = :opcao_quilometragem_id"; $parametros[":opcao_quilometragem_id"] = $data->opcao_quilometragem_id; }
        if (property_exists($data, 'pacote_protecao_id')) { $campos[] = "pacote_protecao_id = :pacote_protecao_id"; $parametros[":pacote_protecao_id"] = $data->pacote_protecao_id; }

        if (isset($data->data_retirada)) { $campos[] = "data_retirada = :data_retirada"; $parametros[":data_retirada"] = $data->data_retirada; }
        if (isset($data->data_devolucao)) { $campos[] = "data_devolucao = :data_devolucao"; $parametros[":data_devolucao"] = $data->data_devolucao; }
        if (isset($data->local_retirada)) { $campos[] = "local_retirada = :local_retirada"; $parametros[":local_retirada"] = $data->local_retirada; }
        if (isset($data->local_devolucao)) { $campos[] = "local_devolucao = :local_devolucao"; $parametros[":local_devolucao"] = $data->local_devolucao; }
        
        if (isset($data->status)) { $campos[] = "status = :status"; $parametros[":status"] = $data->status; }
        if (property_exists($data, 'ativo')) { $campos[] = "ativo = :ativo"; $parametros[":ativo"] = $data->ativo; }

        if (isset($data->valor_diarias)) { $campos[] = "valor_diarias = :valor_diarias"; $parametros[":valor_diarias"] = $data->valor_diarias; }
        if (isset($data->valor_protecao)) { $campos[] = "valor_protecao = :valor_protecao"; $parametros[":valor_protecao"] = $data->valor_protecao; }
        if (property_exists($data, 'valor_desconto')) { $campos[] = "valor_desconto = :valor_desconto"; $parametros[":valor_desconto"] = $data->valor_desconto; }
        if (property_exists($data, 'taxa_aluguel_percentual')) { $campos[] = "taxa_aluguel_percentual = :taxa_aluguel_percentual"; $parametros[":taxa_aluguel_percentual"] = $data->taxa_aluguel_percentual; }
        if (isset($data->valor_total_previsto)) { $campos[] = "valor_total_previsto = :valor_total_previsto"; $parametros[":valor_total_previsto"] = $data->valor_total_previsto; }

        if (empty($campos)) {
            return ["status_code" => 400, "body" => ["erro" => "Nenhum campo válido fornecido para atualização."]];
        }

        try {
            $linhasAfetadas = $this->model->update($id, $campos, $parametros);
            if ($linhasAfetadas > 0) {
                return ["status_code" => 200, "body" => ["status" => "success", "mensagem" => "Reserva atualizada com sucesso."]];
            }
            return ["status_code" => 404, "body" => ["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]];
        } catch (PDOException $e) {
            return ["status_code" => 500, "body" => ["erro" => "Erro interno ao atualizar a reserva: " . $e->getMessage()]];
        }
    }
}
?>