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

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $campos, $parametros) {
        $query = "UPDATE reservas SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute($parametros);
        
        return $stmt->rowCount();
    }

    public function verificarConflito($veiculo_id, $data_retirada, $data_devolucao, $reserva_id_ignorar = null) {
        $query = "SELECT COUNT(*) FROM reservas 
                  WHERE veiculo_id = :veiculo_id 
                  AND status != 'cancelada' 
                  AND (:data_retirada < data_devolucao AND :data_devolucao > data_retirada)";
        if ($reserva_id_ignorar !== null) {
            $query .= " AND id != :reserva_id_ignorar";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":veiculo_id", $veiculo_id);
        $stmt->bindParam(":data_retirada", $data_retirada);
        $stmt->bindParam(":data_devolucao", $data_devolucao);
        if ($reserva_id_ignorar !== null) {
            $stmt->bindParam(":reserva_id_ignorar", $reserva_id_ignorar);
        }
        
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    public function findByVeiculoId($veiculo_id) {
        $query = "SELECT data_retirada, data_devolucao FROM reservas 
                  WHERE veiculo_id = :veiculo_id AND status != 'cancelada'";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":veiculo_id", $veiculo_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByUserId($usuario_id) {
        $query = "SELECT r.id, r.data_retirada, r.data_devolucao, r.local_retirada, r.local_devolucao,
                         r.valor_total_previsto, r.status, r.criado_em,
                         v.modelo AS veiculo_modelo, v.imagem_url AS veiculo_imagem,
                         pp.nome AS protecao_nome, oq.nome AS km_nome
                  FROM reservas r
                  JOIN veiculos v ON r.veiculo_id = v.id
                  LEFT JOIN pacotes_protecao pp ON r.pacote_protecao_id = pp.id
                  LEFT JOIN opcoes_quilometragem oq ON r.opcao_quilometragem_id = oq.id
                  WHERE r.usuario_id = :usuario_id
                  ORDER BY r.criado_em DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>