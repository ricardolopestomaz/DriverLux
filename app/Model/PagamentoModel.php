<?php

require_once __DIR__ . '/../../config/db_connect.php';

class PagamentoModel {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarTodos() {

        $query = "SELECT 
                    p.id,
                    p.tipo_cartao,
                    p.valor_total,
                    p.parcelas,
                    p.status,
                    u.nome AS cliente,
                    r.id AS reserva_id,
                    r.data_retirada,
                    r.data_devolucao,
                    p.criado_em
                  FROM pagamentos p
                  JOIN usuarios u ON p.usuario_id = u.id
                  JOIN reservas r ON p.reserva_id = r.id
                  ORDER BY p.criado_em DESC";

        $stmt = $this->db->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function buscarPorId($id) {

        $query = "SELECT * FROM pagamentos WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ":id" => $id
        ]);

        return $stmt->fetch();
    }

    public function inserir($data, $usuario_id, $parcelas) {

        $query = "INSERT INTO pagamentos (
                    reserva_id,
                    usuario_id,
                    tipo_cartao,
                    valor_total,
                    parcelas,
                    nome_titular,
                    numero_cartao,
                    mes_vencimento,
                    ano_vencimento
                  ) VALUES (
                    :reserva_id,
                    :usuario_id,
                    :tipo_cartao,
                    :valor_total,
                    :parcelas,
                    :nome_titular,
                    :numero_cartao,
                    :mes_vencimento,
                    :ano_vencimento
                  )";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ":reserva_id" => $data->reserva_id,
            ":usuario_id" => $usuario_id,
            ":tipo_cartao" => $data->tipo_cartao,
            ":valor_total" => $data->valor_total,
            ":parcelas" => $parcelas,
            ":nome_titular" => $data->nome_titular,
            ":numero_cartao" => $data->numero_cartao,
            ":mes_vencimento" => $data->mes_vencimento,
            ":ano_vencimento" => $data->ano_vencimento
        ]);
    }

    public function atualizarStatus($id, $status) {

        $query = "UPDATE pagamentos
                  SET status = :status
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ":id" => $id,
            ":status" => $status
        ]);

        return $stmt->rowCount() > 0;
    }

    public function confirmarReserva($reserva_id) {

        $query = "UPDATE reservas
                  SET status = 'confirmada'
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ":id" => $reserva_id
        ]);
    }

    public function verificarReservaUsuario($reserva_id, $usuario_id) {

        $query = "SELECT id
                  FROM reservas
                  WHERE id = :reserva_id
                  AND usuario_id = :usuario_id";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ":reserva_id" => $reserva_id,
            ":usuario_id" => $usuario_id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function verificarPagamentoDuplicado($reserva_id) {

        $query = "SELECT id
                  FROM pagamentos
                  WHERE reserva_id = :reserva_id
                  AND status = 'aprovado'";

        $stmt = $this->db->prepare($query);

        $stmt->execute([
            ":reserva_id" => $reserva_id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function registrarUsoCupomDaReserva($reserva_id) {
        $query = "SELECT cupom_id FROM reservas WHERE id = :reserva_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([":reserva_id" => $reserva_id]);
        $res = $stmt->fetch();
        if ($res && !empty($res['cupom_id'])) {
            $cupom_id = $res['cupom_id'];
            $queryUpdate = "UPDATE cupons SET usos_atuais = usos_atuais + 1 WHERE id = :cupom_id";
            $stmtUpdate = $this->db->prepare($queryUpdate);
            $stmtUpdate->execute([":cupom_id" => $cupom_id]);
        }
    }
}