<?php

class UsuarioControllerTest extends BaseControllerTestCase {

    private $controller;
    private $serviceMock;

    protected function setUp(): void {
        $this->serviceMock = $this->createMock(UsuarioService::class);
        $this->controller = new UsuarioController();
        $this->injectMockService($this->controller, $this->serviceMock);
        
        // Limpa a sessão antes de cada teste
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        session_destroy();
    }

    public function testGetUsuariosListagem() {
        $mockResponse = [
            "status_code" => 200,
            "body" => [["id" => 1, "nome" => "João"]]
        ];
        $this->serviceMock->method('listarUsuarios')->willReturn($mockResponse);

        $_SERVER['REQUEST_URI'] = '/api/usuarios';

        ob_start();
        $this->controller->handleRequest('GET', null);
        $output = ob_get_clean();

        $this->assertEquals(200, http_response_code());
        $this->assertJsonStringEqualsJsonString(json_encode($mockResponse['body']), $output);
    }

    public function testUpdateUsuarioSemSessaoRetorna401() {
        // Tentativa de update sem $_SESSION['usuario_id']
        ob_start();
        $this->controller->handleRequest('PUT', 1);
        $output = ob_get_clean();

        $this->assertEquals(401, http_response_code());
        $this->assertStringContainsString("Acesso negado", $output);
    }
}