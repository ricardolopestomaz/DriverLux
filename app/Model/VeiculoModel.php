<?php

require_once __DIR__ . '/../../config/db_connect.php';

class VeiculoModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findAll() {
        $query = "SELECT 
                    v.id, v.marca, v.modelo, v.ano, v.placa, v.status_disponibilidade, v.imagem_url,
                    c.nome AS categoria_nome, c.valor_base_diaria 
                  FROM veiculos v
                  LEFT JOIN categorias_veiculos c ON v.categoria_id = c.id
                  WHERE v.ativo = TRUE";
                  
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT 
                    v.id, v.marca, v.modelo, v.ano, v.placa, v.chassi, v.status_disponibilidade, v.imagem_url,
                    c.nome AS categoria_nome, c.valor_base_diaria 
                  FROM veiculos v
                  LEFT JOIN categorias_veiculos c ON v.categoria_id = c.id
                  WHERE v.id = :id AND v.ativo = TRUE";
                  
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO veiculos (categoria_id, marca, modelo, ano, placa, chassi) 
                  VALUES (:categoria_id, :marca, :modelo, :ano, :placa, :chassi)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":categoria_id", $data->categoria_id);
        $stmt->bindParam(":marca", $data->marca);
        $stmt->bindParam(":modelo", $data->modelo);
        $stmt->bindParam(":ano", $data->ano);
        $stmt->bindParam(":placa", $data->placa);
        $stmt->bindParam(":chassi", $data->chassi);

        return $stmt->execute();
    }

    public function update($id, $campos, $parametros) {
        $query = "UPDATE veiculos SET " . implode(", ", $campos) . " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute($parametros);
        
        return $stmt->rowCount();
    }
}
?>