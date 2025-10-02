<?php

declare(strict_types=1);

namespace VendingMachine\Tests\Entrypoint\Http\Controllers;

use VendingMachine\Entrypoint\Http\Controllers\HelloWorldController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * HelloWorldController Test
 *
 * Given: A HelloWorldController instance
 * When: We call its methods
 * Then: It should return proper responses
 */
class HelloWorldControllerTest extends TestCase
{
    private HelloWorldController $controller;

    protected function setUp(): void
    {
        $this->controller = new HelloWorldController();
    }

    public function testHelloReturnsJsonResponse(): void
    {
        // When
        $response = $this->controller->hello();

        // Then
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Hello, World!', $data['message']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals('dev', $data['environment']);
    }

    public function testHelloNameReturnsJsonResponse(): void
    {
        // Given
        $name = 'TestUser';

        // When
        $response = $this->controller->helloName($name);

        // Then
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals("Hello, {$name}!", $data['message']);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertEquals('dev', $data['environment']);
    }
}
