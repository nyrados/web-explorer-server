<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Http\Utils\Handler\ResponseHandler;
use Nyrados\Http\Utils\Middleware\RangeMiddleware;
use Nyrados\Utils\File\File;

use function GuzzleHttp\Psr7\stream_for;

class FileView extends AbstractMiddleware
{
    public function run(File $file, array $params = [])
    {
        $file->assertIsFile();
        $body = stream_for(fopen($file->getWrapper(), 'r'));

        $response = $this->response
            ->withBody($body)
            ->withHeader('Content-Type', mime_content_type($this->file->getWrapper()))
        ;

        return (new RangeMiddleware())->process($this->request, new ResponseHandler($response));
    }
}
