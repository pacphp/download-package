<?php
declare(strict_types=1);

namespace Test\Unit\Middleware;

use AspectMock\Test as Mock;
use Http\Factory\Diactoros\RequestFactory;
use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Pac\Download\Factory\DownloadResponseFactory;
use Pac\Download\Middleware\DownloadMiddleware;
use Pac\Download\Security\PermissiveAuthorizationCheck;
use Pac\Pipe;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnitTester;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Diactoros\Uri;

class DownloadMiddlewareCest
{
    private $defaultMiddleware;
    private $filesystem;
    private $nullMiddlewareMock;
    private $pipe;
    private $responseFactory;
    private $requestFactory;

    public function _before()
    {
        $adapter = new MemoryAdapter();
        $this->filesystem = new Filesystem($adapter);
        $this->responseFactory = new DownloadResponseFactory($this->filesystem);
        $this->requestFactory = new RequestFactory();
        $this->defaultMiddleware = new DownloadMiddleware($this->responseFactory);
        $this->pipe = new Pipe([new NullMiddleware()]);
    }

    public function _after(UnitTester $I)
    {
        Mock::clean();
    }

    public function testDefault(UnitTester $I)
    {
        $factoryMock = Mock::double(DownloadResponseFactory::class, ['create' => new Response()]);
        $securityCheckMock = Mock::double(PermissiveAuthorizationCheck::class, ['allowed' => true]);
        $uri = new Uri('http://localhost:8080/file?8675309.test.png');
        $request = new ServerRequest([], [], $uri, 'GET');
        $this->defaultMiddleware->process($request, $this->pipe);

        $securityCheckMock->verifyInvoked('allowed');
        $factoryMock->verifyInvoked('create');
    }

    public function testWrongMethod(UnitTester $I)
    {
        $pipeMiddlewareMock = Mock::double($this->pipe, ['handle' => null]);
        $uri = new Uri('http://localhost:8080/file?8675309.test.png');
        $request = new ServerRequest([], [], $uri, 'POST');

        $response = (new DownloadMiddleware($this->responseFactory))->process($request, $this->pipe);

        // hack, I can't get the mock to verify
        $I->assertEquals('From Null', $response->getBody(), 'The next middleware should be called');
        // $pipeMiddlewareMock->verifyInvoked('handle');
    }

    public function testWrongPath(UnitTester $I)
    {
        $pipeMiddlewareMock = Mock::double($this->pipe, ['handle' => null]);
        $uri = new Uri('http://localhost:8080/not-file?8675309.test.png');
        $request = new ServerRequest([], [], $uri, 'GET');

        $response = (new DownloadMiddleware($this->responseFactory))->process($request, $this->pipe);

        // hack, I can't get the mock to verify
        $I->assertEquals('From Null', $response->getBody(), 'The next middleware should be called');
        // $pipeMiddlewareMock->verifyInvoked('handle');
    }

    public function testUnauthorized(UnitTester $I)
    {
        Mock::double(PermissiveAuthorizationCheck::class, ['allowed' => false]);
        $uri = new Uri('http://localhost:8080/file?8675309.test.png');
        $request = new ServerRequest([], [], $uri, 'GET');
        $response = $this->defaultMiddleware->process($request, $this->pipe);

        $I->assertSame(403, $response->getStatusCode());
    }

}

class NullMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = new Response();
            $response->getBody()->write('From Null');

            return $response;
    }
}
