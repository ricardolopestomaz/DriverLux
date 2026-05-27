<?php

require_once __DIR__ . '/../Service/PagamentoService.php';

class PagamentoController {

    private $service;

    public function __construct() {
        $this->service = new PagamentoService();
    }

    public function handleRequest($method, $id = null) {

        switch ($method) {

            case 'GET':

                if ($id) {
                    $resultado = $this->service->buscarPagamento($id);
                } else {
                    $resultado = $this->service->listarPagamentos();
                }

                break;

            case 'POST':

                $data = json_decode(file_get_contents("php://input"));

                $resultado = $this->service->criarPagamento($data);

                break;

            case 'PUT':

                $data = json_decode(file_get_contents("php://input"));

                $resultado = $this->service->atualizarPagamento($id, $data);

                break;

            default:

                http_response_code(405);

                echo json_encode([
                    "erro" => "Método HTTP não permitido."
                ]);

                return;
        }

        http_response_code($resultado["code"]);

        echo json_encode($resultado["data"]);
    }
}