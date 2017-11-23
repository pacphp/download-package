<?php
declare(strict_types=1);

namespace Pac\Download\Middleware;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use League\Flysystem\Filesystem;
use Pac\Download\Security\AuthorizationCheckInterface;
use Pac\Download\Security\PermissiveAuthorizationCheck;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DownloadMiddleware implements MiddlewareInterface
{
    private $authorizationCheck;
    private $routeName;

    public function __construct(Filesystem $filesystem, array $options = [], AuthorizationCheckInterface $authorizationCheck = null)
    {
        $this->routeName = $options['routeName'] ?? 'file';
        $this->routeName = $options['verb'] ?? 'GET';
        $this->authorizationCheck = $authorizationCheck ?? new PermissiveAuthorizationCheck();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

    }
}
