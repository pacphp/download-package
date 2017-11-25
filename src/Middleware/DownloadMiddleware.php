<?php
declare(strict_types=1);

namespace Pac\Download\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Pac\Download\Factory\DownloadResponseFactory;
use Pac\Download\Security\AuthorizationCheckInterface;
use Pac\Download\Security\PermissiveAuthorizationCheck;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class DownloadMiddleware implements MiddlewareInterface
{
    private $authorizationCheck;
    private $responseFactory;
    private $path;
    private $verb;

    public function __construct(DownloadResponseFactory $responseFactory, array $options = [], AuthorizationCheckInterface $authorizationCheck = null)
    {
        $this->responseFactory = $responseFactory;
        $pathName = $options['routeName'] ?? 'file';
        $this->path = '/' . ltrim($pathName, '/');
        $this->verb = $options['verb'] ?? 'GET';
        $this->authorizationCheck = $authorizationCheck ?? new PermissiveAuthorizationCheck();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->verb !== $request->getMethod()) {
            return $handler->handle($request);
        }
        if ($this->path !== $request->getUri()->getPath()) {
            return $handler->handle($request);
        }

        $filename = $request->getUri()->getQuery();
        if (!$this->authorizationCheck->allowed($filename)) {
            return new JsonResponse([], 403);
        }

        return $this->responseFactory->create($filename);
    }
}
