<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/BaseControllerTestCase.php';
require_once __DIR__ . '/../app/Controller/CupomController.php';
require_once __DIR__ . '/../app/Service/CupomService.php';

class CupomControllerTest extends BaseControllerTestCase {

    private $controller;
    private $serviceMock;

    protected function setUp(): void {
        $this->serviceMock = $this->createMock(CupomService::class);
        $this->controller = new CupomController();
        $this->injectMockService($this->controller, $this->serviceMock);
    }

    public function testListarCupons() {
        $mockResult = ["status_code" => 200, "body" => ["status" => "success", "cupons" => []]];
        $this->serviceMock->method('listarCupons')->willReturn($mockResult);

        ob_start();
        $this->controller->handleRequest('GET', null);
        $output = ob_get_clean();

        $this->assertEquals(200, http_response_code());
    }
}