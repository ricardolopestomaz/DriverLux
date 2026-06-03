<?php

require_once __DIR__ . '/../../config/db_connect.php';

class ProtecaoModel {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarTodas() {

        $query = "SELECT
                    id,
                    nome,
                    descricao,
                    valor_diario,
                    ativo
                  FROM pacotes_protecao";

        $stmt = $this->db->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function inserir($data) {

        $query = "INSERT INTO pacotes_protecao
                    (
                        nome,
                        descricao,
                        valor_diario
                    )
                  VALUES
                    (
                        :nome,
                        :descricao,
                        :valor_diario
                    )";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ":nome" => $data->nome,
            ":descricao" => $data->descricao ?? null,
            ":valor_diario" => $data->valor_diario
        ]);
    }

    public function atualizar($id, $data) {

        $campos = [];
        $parametros = [
            ":id" => $id
        ];

        if (isset($data->nome)) {
            $campos[] = "nome = :nome";
            $parametros[":nome"] = $data->nome;
        }

        if (isset($data->descricao)) {
            $campos[] = "descricao = :descricao";
            $parametros[":descricao"] = $data->descricao;
        }

        if (isset($data->valor_diario)) {
            $campos[] = "valor_diario = :valor_diario";
            $parametros[":valor_diario"] = $data->valor_diario;
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

        $query = "UPDATE pacotes_protecao
                  SET " . implode(", ", $campos) . "
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->execute($parametros);

        return [
            "success" => $stmt->rowCount() > 0
        ];
    }
}