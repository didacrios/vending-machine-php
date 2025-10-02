<?php

declare(strict_types=1);

namespace VendingMachine\Entrypoint\Http\Controllers;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Hello World Controller
 *
 * Example controller demonstrating basic Symfony functionality
 */
class HelloWorldController extends AbstractController
{
    #[Route('/hello', name: 'hello_world', methods: ['GET'])]
    public function hello(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => 'dev'
        ]);
    }

    #[Route('/hello/{name}', name: 'hello_name', methods: ['GET'])]
    public function helloName(string $name): JsonResponse
    {
        return new JsonResponse([
            'message' => "Hello, {$name}!",
            'timestamp' => date('Y-m-d H:i:s'),
            'environment' => 'dev'
        ]);
    }
}
