<?php

require_once __DIR__ . '/../Model/VeiculoModel.php';

class VeiculoService {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function listarVeiculos() {
        $veiculos = $this->model->findAll();
        return [
            "status_code" => 200,
            "body" => [
                "status" => "success",
                "total" => count($veiculos),
                "data" => $veiculos
            ]
        ];
    }

    public function buscarVeiculo($id) {
        $veiculo = $this->model->findById($id);
        
        if ($veiculo) {
            return ["status_code" => 200, "body" => ["status" => "success", "data" => $veiculo]];
        }
        
        return ["status_code" => 404, "body" => ["erro" => "Veículo não encontrado."]];
    }

    public function criarVeiculo($data) {
        if (empty($data->categoria_id) || empty($data->marca) || empty($data->modelo) || empty($data->placa)) {
            return ["status_code" => 400, "body" => ["erro" => "Dados incompletos. Categoria, marca, modelo e placa são obrigatórios."]];
        }

        try {
            $this->model->create($data);
            return ["status_code" => 201, "body" => ["mensagem" => "Veículo cadastrado com sucesso."]];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ["status_code" => 400, "body" => ["erro" => "Placa ou Chassi já cadastrados."]];
            }
            return ["status_code" => 400, "body" => ["erro" => "Erro ao cadastrar veículo: " . $e->getMessage()]];
        }
    }

    public function atualizarVeiculo($id, $data) {
        if (empty($id)) {
            return ["status_code" => 400, "body" => ["erro" => "O ID do veículo é obrigatório para atualização."]];
        }

        if (empty((array)$data)) {
            return ["status_code" => 400, "body" => ["erro" => "Nenhum dado enviado para atualização."]];
        }

        $campos = [];
        $parametros = [":id" => $id];

        if (isset($data->categoria_id)) { $campos[] = "categoria_id = :categoria_id"; $parametros[":categoria_id"] = $data->categoria_id; }
        if (isset($data->marca)) { $campos[] = "marca = :marca"; $parametros[":marca"] = $data->marca; }
        if (isset($data->modelo)) { $campos[] = "modelo = :modelo"; $parametros[":modelo"] = $data->modelo; }
        if (isset($data->ano)) { $campos[] = "ano = :ano"; $parametros[":ano"] = $data->ano; }
        if (isset($data->placa)) { $campos[] = "placa = :placa"; $parametros[":placa"] = $data->placa; }
        if (isset($data->chassi)) { $campos[] = "chassi = :chassi"; $parametros[":chassi"] = $data->chassi; }
        if (isset($data->status_disponibilidade)) { $campos[] = "status_disponibilidade = :status_disponibilidade"; $parametros[":status_disponibilidade"] = $data->status_disponibilidade; }
        if (isset($data->imagem_url)) { $campos[] = "imagem_url = :imagem_url"; $parametros[":imagem_url"] = $data->imagem_url; }
        if (property_exists($data, 'ativo')) { $campos[] = "ativo = :ativo"; $parametros[":ativo"] = $data->ativo; }

        if (empty($campos)) {
            return ["status_code" => 400, "body" => ["erro" => "Nenhum campo válido fornecido para atualização."]];
        }

        try {
            $linhasAfetadas = $this->model->update($id, $campos, $parametros);
            if ($linhasAfetadas > 0) {
                return ["status_code" => 200, "body" => ["status" => "success", "mensagem" => "Veículo atualizado com sucesso."]];
            }
            return ["status_code" => 404, "body" => ["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ["status_code" => 400, "body" => ["erro" => "Placa ou Chassi já cadastrados em outro veículo."]];
            }
            return ["status_code" => 400, "body" => ["erro" => "Erro interno ao atualizar veículo: " . $e->getMessage()]];
        }
    }
}
?>