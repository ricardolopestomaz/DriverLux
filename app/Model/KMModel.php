<?php

require_once __DIR__ . '/../../config/db_connect.php';

class KMModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function listar() {
        $sql = "SELECT id, nome, limite_km, valor_diario, taxa_km_excedente, ativo 
                FROM opcoes_quilometragem";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function criar($data) {
        $sql = "INSERT INTO opcoes_quilometragem 
                (nome, limite_km, valor_diario, taxa_km_excedente)
                VALUES (:nome, :limite_km, :valor_diario, :taxa_km_excedente)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ":nome" => $data->nome,
            ":limite_km" => $data->limite_km ?? null,
            ":valor_diario" => $data->valor_diario,
            ":taxa_km_excedente" => $data->taxa_km_excedente ?? null
        ]);
    }

    public function atualizar($id, $campos, $parametros) {
        $sql = "UPDATE opcoes_quilometragem 
                SET " . implode(", ", $campos) . " 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->rowCount();
    }
}