<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/BaseControllerTestCase.php';
require_once __DIR__ . '/../app/Controller/VeiculoController.php';
require_once __DIR__ . '/../app/Service/VeiculoService.php';
require_once __DIR__ . '/../app/Model/VeiculoModel.php'; 

class VeiculoControllerTest extends BaseControllerTestCase {
// ...

    private $controller;
    private $serviceMock;

    protected function setUp(): void {
        $this->serviceMock = $this->createMock(VeiculoService::class);
        $this->controller = new VeiculoController();
        $this->injectMockService($this->controller, $this->serviceMock);
        
        @session_start();
    }

    public function testPostCriaVeiculoComAdminSessao() {
        // Configura sessão como admin
        $_SESSION['usuario_id'] = 1;
        $_SESSION['usuario_perfil'] = 'admin';

        $mockResponse = [
            "status_code" => 201,
            "body" => ["mensagem" => "Veículo criado"]
        ];
        $this->serviceMock->method('criarVeiculo')->willReturn($mockResponse);

        ob_start();
        $this->controller->handleRequest('POST', null);
        $output = ob_get_clean();

        $this->assertEquals(201, http_response_code());
        $this->assertStringContainsString("Veículo criado", $output);
    }

    public function testPostRetorna403ParaUsuarioComum() {
        // Configura sessão como usuário comum
        $_SESSION['usuario_id'] = 2;
        $_SESSION['usuario_perfil'] = 'cliente';

        ob_start();
        $this->controller->handleRequest('POST', null);
        $output = ob_get_clean();

        $this->assertEquals(403, http_response_code());
        $this->assertStringContainsString("Apenas administradores podem", $output);
    }
}