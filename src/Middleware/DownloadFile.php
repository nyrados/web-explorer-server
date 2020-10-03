<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Http\Utils\Handler\ResponseHandler;
use Nyrados\Utils\File\File;

class DownloadFile extends AbstractMiddleware
{
    protected static $method = 'GET';

    public function run(File $file, array $params = [])
    {
        $view = new FileView($this->we);
        $response = $view->process($this->request, new ResponseHandler($this->response));

        if ( ((string) $response->getStatusCode())[0] !== '2') {
            return $response;
        }

        return $response->withHeader('Content-Disposition', 'attachment; filename="' . $file->getName() . '"');
    }
}