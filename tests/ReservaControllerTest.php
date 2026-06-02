<?php

require_once __DIR__ . '/BaseControllerTestCase.php';
require_once __DIR__ . '/../app/Controller/ReservaController.php';
require_once __DIR__ . '/../app/Service/ReservaService.php';

class ReservaControllerTest extends BaseControllerTestCase {

    private $controller;
    private $serviceMock;

    protected function setUp(): void {
        $this->serviceMock = $this->createMock(ReservaService::class);
        $this->controller = new ReservaController();
        $this->injectMockService($this->controller, $this->serviceMock);
        
        @session_start();
    }

    public function testGetBuscaReservaEspecifica() {
        $mockResponse = [
            "status_code" => 200,
            "body" => ["id" => 5, "status" => "ativa"]
        ];
        $this->serviceMock->method('buscarReserva')->with(5)->willReturn($mockResponse);

        ob_start();
        $this->controller->handleRequest('GET', 5);
        $output = ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode($mockResponse['body']), $output);
    }

    public function testPostRetorna401SeNaoLogado() {
        session_destroy(); // Garante ausência de sessão
        
        ob_start();
        $this->controller->handleRequest('POST', null);
        $output = ob_get_clean();

        $this->assertEquals(401, http_response_code());
        $this->assertStringContainsString("Você precisa fazer login para interagir com reservas", $output);
    }
}