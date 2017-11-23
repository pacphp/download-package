<?php
declare(strict_types=1);

namespace Test\Unit\Middleware;

use AspectMock\Test as Mock;
use Http\Factory\Diactoros\RequestFactory;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Pac\Download\Factory\DownloadResponseFactory;
use Pac\Download\Middleware\DownloadMiddleware;
use UnitTester;
use Zend\Diactoros\Response;

class DownloadMiddlewareCest
{
    private $defaultMiddleware;
    private $filesystem;
    private $requestFactory;

    public function __construct()
    {
        $adapter = new MemoryAdapter();
        $this->filesystem = new Filesystem($adapter);
        $this->defaultMiddleware = new DownloadMiddleware($this->filesystem);
        $this->requestFactory = new RequestFactory();
    }

    public function testDefault(UnitTester $I)
    {
        $factoryMock = Mock::double(DownloadResponseFactory::class, ['create' => new Response()]);
        $request = $this->requestFactory->createRequest('GET', 'http://localhost:8080/file?8675309.test.png');

        $factoryMock->verifyInvoked('create');
    }
}
