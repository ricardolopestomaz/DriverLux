<?php

require_once __DIR__ . '/../service/CupomService.php';

class CupomController {

    private $service;

    public function __construct() {
        $this->service = new CupomService();
    }

    public function handleRequest($method, $id = null) {

        switch ($method) {

            case 'GET':
                $resultado = $this->service->listarCupons();
                break;

            case 'POST':
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->criarCupom($data);
                break;

            case 'PUT':
                $data = json_decode(file_get_contents("php://input"));
                $resultado = $this->service->atualizarCupom($id, $data);
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