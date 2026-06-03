<?php

require_once __DIR__ . '/BaseControllerTestCase.php';
require_once __DIR__ . '/../app/Controller/KMController.php';
require_once __DIR__ . '/../app/Service/KMService.php';

class KMControllerTest extends BaseControllerTestCase {

    private $controller;
    private $serviceMock;

    protected function setUp(): void {
        $this->serviceMock = $this->createMock(KMService::class);
        $this->controller = new KMController();
        $this->injectMockService($this->controller, $this->serviceMock);
    }

    public function testGetOpcoesKMRetornaSucesso() {
        $mockResponse = [
            "status_code" => 200,
            "body" => [
                "status" => "success",
                "total" => 1,
                "data" => [["id" => 1, "nome" => "Livre"]]
            ]
        ];
        $this->serviceMock->method('listarOpcoes')->willReturn($mockResponse);

        ob_start();
        $this->controller->handleRequest('GET', null);
        $output = ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertStringContainsString("success", $output);
        $this->assertStringContainsString("Livre", $output);
    }

    public function testPutOpcaoKMSemAdminFalha() {
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_perfil'] = 'cliente'; // Falha pois precisa ser 'admin'

        ob_start();
        $this->controller->handleRequest('PUT', 1);
        $output = ob_get_clean();

        $this->assertEquals(403, http_response_code());
        $this->assertStringContainsString("Apenas administradores podem gerenciar", $output);
    }
}