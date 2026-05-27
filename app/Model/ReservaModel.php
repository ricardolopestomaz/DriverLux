<?php

require_once __DIR__ . '/../../config/db_connect.php';

class ReservaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findAll() {
        $query = "SELECT r.id, r.data_retirada, r.data_devolucao, r.valor_total_previsto, r.status,
                         u.nome AS cliente, v.modelo AS veiculo
                  FROM reservas r
                  JOIN usuarios u ON r.usuario_id = u.id
                  JOIN veiculos v ON r.veiculo_id = v.id
                  ORDER BY r.criado_em DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT * FROM reservas WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data, $usuario_id) {
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

        $stmt->bindParam(":usuario_id", $usuario_id);
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

        return $stmt->execute();
    }

    public function update($id, $campos, $parametros) {
        $query = "UPDATE reservas SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute($parametros);
        
        return $stmt->rowCount();
    }
}
?>