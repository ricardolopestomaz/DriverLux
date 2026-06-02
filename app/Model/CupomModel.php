<?php

require_once __DIR__ . '/../../config/db_connect.php';

class CupomModel {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarTodos() {

        $query = "SELECT * FROM cupons WHERE ativo = TRUE";

        $stmt = $this->db->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function inserir($data) {

        $query = "INSERT INTO cupons
                    (
                        codigo,
                        descricao,
                        tipo_desconto,
                        valor_desconto,
                        data_validade,
                        limite_usos
                    )
                  VALUES
                    (
                        :codigo,
                        :descricao,
                        :tipo_desconto,
                        :valor_desconto,
                        :data_validade,
                        :limite_usos
                    )";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ":codigo" => $data->codigo,
            ":descricao" => $data->descricao ?? null,
            ":tipo_desconto" => $data->tipo_desconto,
            ":valor_desconto" => $data->valor_desconto,
            ":data_validade" => $data->data_validade,
            ":limite_usos" => $data->limite_usos ?? null
        ]);
    }

    public function atualizar($id, $data) {

        $campos = [];
        $parametros = [":id" => $id];

        if (isset($data->codigo)) {
            $campos[] = "codigo = :codigo";
            $parametros[":codigo"] = $data->codigo;
        }

        if (isset($data->descricao)) {
            $campos[] = "descricao = :descricao";
            $parametros[":descricao"] = $data->descricao;
        }

        if (isset($data->tipo_desconto)) {
            $campos[] = "tipo_desconto = :tipo_desconto";
            $parametros[":tipo_desconto"] = $data->tipo_desconto;
        }

        if (isset($data->valor_desconto)) {
            $campos[] = "valor_desconto = :valor_desconto";
            $parametros[":valor_desconto"] = $data->valor_desconto;
        }

        if (isset($data->data_inicio)) {
            $campos[] = "data_inicio = :data_inicio";
            $parametros[":data_inicio"] = $data->data_inicio;
        }

        if (isset($data->data_validade)) {
            $campos[] = "data_validade = :data_validade";
            $parametros[":data_validade"] = $data->data_validade;
        }

        if (isset($data->limite_usos)) {
            $campos[] = "limite_usos = :limite_usos";
            $parametros[":limite_usos"] = $data->limite_usos;
        }

        if (isset($data->ativo)) {
            $campos[] = "ativo = :ativo";
            $parametros[":ativo"] = $data->ativo;
        }

        if (empty($campos)) {

            return [
                "success" => false
            ];
        }

        $query = "UPDATE cupons
                  SET " . implode(", ", $campos) . "
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->execute($parametros);

        return [
            "success" => $stmt->rowCount() > 0
        ];
    }

    public function buscarPorCodigo($codigo) {
        $query = "SELECT * FROM cupons 
                  WHERE codigo = :codigo 
                  AND ativo = TRUE 
                  AND (data_validade >= NOW() OR data_validade IS NULL)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([":codigo" => $codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}