<?php

declare(strict_types=1);

use App\Domain\Exception\CartEmptyException;
use App\Domain\Exception\CartNotFoundException;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Exception\ProductNotFoundException;
use App\Http\Controller\CartController;
use App\Http\Controller\OrderController;
use DI\ContainerBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = parse_ini_file(__DIR__ . '/../.env');
if (is_array($dotenv)) {
    foreach ($dotenv as $key => $value) {
        if (is_string($value) || is_int($value) || is_float($value)) {
            $val = (string) $value;
            $_ENV[$key] = $val;
            putenv("{$key}={$val}");
        }
    }
}

$builder = new ContainerBuilder();
/** @var array<string, mixed> $definitions */
$definitions = require __DIR__ . '/../bootstrap/container.php';
$builder->addDefinitions($definitions);
$container = $builder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(
    function (ServerRequestInterface $request, \Throwable $e) use ($app): ResponseInterface {
        $status = match (true) {
            $e instanceof CartNotFoundException,
            $e instanceof ProductNotFoundException,
            $e instanceof OrderNotFoundException => 404,
            $e instanceof CartEmptyException,
            $e instanceof \InvalidArgumentException => 422,
            default => 500,
        };

        $response = $app->getResponseFactory()->createResponse($status);
        $response->getBody()->write(json_encode([
            'error' => $e->getMessage(),
        ], JSON_UNESCAPED_UNICODE) ?: '{}');

        return $response->withHeader('Content-Type', 'application/json');
    },
);

$app->get('/health', function (
    ServerRequestInterface $request,
    ResponseInterface $response
): ResponseInterface {
    $response->getBody()->write(json_encode([
        'status' => 'ok',
    ]) ?: '{}');
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/api/cart', [CartController::class, 'create']);
$app->get('/api/cart/{id}', [CartController::class, 'get']);
$app->post('/api/cart/add', [CartController::class, 'addItem']);
$app->post('/api/cart/remove', [CartController::class, 'removeItem']);

$app->post('/api/orders', [OrderController::class, 'create']);
$app->get('/api/orders', [OrderController::class, 'getAll']);
$app->get('/api/orders/{id}', [OrderController::class, 'get']);

$app->run();
