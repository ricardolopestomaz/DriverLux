<?php

use PHPUnit\Framework\TestCase;

// 1. Importa a classe Base de Testes que você criou
require_once __DIR__ . '/BaseControllerTestCase.php';

// 2. Importa o Controller que vamos testar e o Service que vamos "mockar" (simular)
require_once __DIR__ . '/../app/Controller/CategoriaController.php';
require_once __DIR__ . '/../app/Service/CategoriaService.php';

class CategoriaControllerTest extends BaseControllerTestCase {

    private $controller;
    private $serviceMock;

    protected function setUp(): void {
        // Criamos o mock do serviço
        $this->serviceMock = $this->createMock(CategoriaService::class);
        
        // Instanciamos o controller e injetamos o mock
        $this->controller = new CategoriaController();
        $this->injectMockService($this->controller, $this->serviceMock);
    }

    public function testHandleRequestGetRetornaListaDeCategorias() {
        // Define o que o mock deve retornar
        $mockResult = [
            "status_code" => 200,
            "body" => ["status" => "success", "categorias" => []]
        ];
        
        // Diz para o mock: "Quando chamarem o listarCategorias(), retorne $mockResult"
        $this->serviceMock->method('listarCategorias')->willReturn($mockResult);

        // Captura a saída do echo usando output buffering
        ob_start();
        $this->controller->handleRequest('GET');
        $output = ob_get_clean();

        // Verificações
        $this->assertEquals(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode($mockResult['body']), $output);
    }

    public function testHandleRequestMetodoInvalido() {
        // Captura a saída
        ob_start();
        $this->controller->handleRequest('DELETE');
        $output = ob_get_clean();

        // Verificações
        $this->assertEquals(405, http_response_code());
        $this->assertStringContainsString("Método HTTP não permitido", $output);
    }
}