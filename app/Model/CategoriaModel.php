<?php

require_once __DIR__ . '/../../config/db_connect.php';

class CategoriaModel {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function buscarTodas() {

        $query = "SELECT 
                    id,
                    nome,
                    descricao,
                    valor_base_diaria,
                    imagem_ilustrativa,
                    ativo
                  FROM categorias_veiculos";

        $stmt = $this->db->prepare($query);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function inserir($data) {

        $query = "INSERT INTO categorias_veiculos
                    (
                        nome,
                        descricao,
                        valor_base_diaria,
                        imagem_ilustrativa
                    )
                  VALUES
                    (
                        :nome,
                        :descricao,
                        :valor_base_diaria,
                        :imagem_ilustrativa
                    )";

        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ":nome" => $data->nome,
            ":descricao" => $data->descricao ?? null,
            ":valor_base_diaria" => $data->valor_base_diaria,
            ":imagem_ilustrativa" => $data->imagem_ilustrativa ?? null
        ]);
    }

    public function atualizar($id, $data) {

        $campos = [];
        $parametros = [":id" => $id];

        if (isset($data->nome)) {
            $campos[] = "nome = :nome";
            $parametros[":nome"] = $data->nome;
        }

        if (isset($data->descricao)) {
            $campos[] = "descricao = :descricao";
            $parametros[":descricao"] = $data->descricao;
        }

        if (isset($data->valor_base_diaria)) {
            $campos[] = "valor_base_diaria = :valor_base_diaria";
            $parametros[":valor_base_diaria"] = $data->valor_base_diaria;
        }

        if (isset($data->imagem_ilustrativa)) {
            $campos[] = "imagem_ilustrativa = :imagem_ilustrativa";
            $parametros[":imagem_ilustrativa"] = $data->imagem_ilustrativa;
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

        $query = "UPDATE categorias_veiculos
                  SET " . implode(", ", $campos) . "
                  WHERE id = :id";

        $stmt = $this->db->prepare($query);

        $stmt->execute($parametros);

        return [
            "success" => $stmt->rowCount() > 0
        ];
    }
}