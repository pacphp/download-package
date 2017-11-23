<?php
declare(strict_types=1);

namespace Pac\Download\Factory;

use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

class DownloadResponseFactory
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function create(string $systemFilename): ResponseInterface
    {
        $metadata = $this->filesystem->getMetadata($systemFilename);
        $headers = [
            'Cache-Control'       => 'public',
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $systemFilename),
            'Content-Length'      => $metadata['size'],
        ];

        return new Response($this->filesystem->readStream($systemFilename), 200, $headers);
    }
}
