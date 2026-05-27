<?php

use PHPUnit\Framework\TestCase;

class BaseControllerTestCase extends TestCase {
    /**
     * Helper para injetar o mock do serviço na propriedade privada do Controller
     */
    protected function injectMockService($controller, $mockService) {
        $reflection = new ReflectionClass($controller);
        $property = $reflection->getProperty('service');
        $property->setAccessible(true);
        $property->setValue($controller, $mockService);
    }
}