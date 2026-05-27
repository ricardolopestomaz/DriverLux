<?php

require_once __DIR__ . '/../Model/kmModel.php';

class KMService {
    private $model;

    public function __construct($db) {
        $this->model = new KMModel($db);
    }

    public function listarOpcoes() {
        return $this->model->listar();
    }

    public function criarOpcao($data) {
        if (empty($data->nome) || !isset($data->valor_diario)) {
            return [
                "status" => 400,
                "resposta" => ["erro" => "Dados incompletos. Nome e valor_diario são obrigatórios."]
            ];
        }

        $criado = $this->model->criar($data);

        if ($criado) {
            return [
                "status" => 201,
                "resposta" => ["mensagem" => "Opção de quilometragem cadastrada com sucesso."]
            ];
        }

        return [
            "status" => 400,
            "resposta" => ["erro" => "Erro ao cadastrar opção de KM."]
        ];
    }

    public function atualizarOpcao($id, $data) {
        if (empty($id)) {
            return [
                "status" => 400,
                "resposta" => ["erro" => "O ID da opção de KM é obrigatório para atualização."]
            ];
        }

        if (empty($data)) {
            return [
                "status" => 400,
                "resposta" => ["erro" => "Nenhum dado enviado para atualização."]
            ];
        }

        $campos = [];
        $parametros = [":id" => $id];

        if (isset($data->nome)) {
            $campos[] = "nome = :nome";
            $parametros[":nome"] = $data->nome;
        }

        if (property_exists($data, 'limite_km')) {
            $campos[] = "limite_km = :limite_km";
            $parametros[":limite_km"] = $data->limite_km;
        }

        if (isset($data->valor_diario)) {
            $campos[] = "valor_diario = :valor_diario";
            $parametros[":valor_diario"] = $data->valor_diario;
        }

        if (property_exists($data, 'taxa_km_excedente')) {
            $campos[] = "taxa_km_excedente = :taxa_km_excedente";
            $parametros[":taxa_km_excedente"] = $data->taxa_km_excedente;
        }

        if (isset($data->ativo)) {
            $campos[] = "ativo = :ativo";
            $parametros[":ativo"] = $data->ativo;
        }

        if (empty($campos)) {
            return [
                "status" => 400,
                "resposta" => ["erro" => "Nenhum campo válido fornecido para atualização."]
            ];
        }

        $alteracoes = $this->model->atualizar($id, $campos, $parametros);

        if ($alteracoes > 0) {
            return [
                "status" => 200,
                "resposta" => ["status" => "success", "mensagem" => "Opção de KM atualizada com sucesso."]
            ];
        }

        return [
            "status" => 404,
            "resposta" => ["status" => "warning", "mensagem" => "Nenhuma alteração feita. ID não encontrado ou dados iguais."]
        ];
    }
}
