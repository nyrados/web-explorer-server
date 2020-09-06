<?php
namespace Nyrados\WebExplorer\Middleware\Selector;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface MiddlewareSelectorInterface
{
    /**
     * Select depending on the request a suitable middleware
     *
     * @param ServerRequestInterface $request
     * @return MiddlewareInterface
     */
    public function selectMiddleware(ServerRequestInterface $request): MiddlewareInterface;
}
