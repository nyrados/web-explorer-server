<?php

namespace Nyrados\WebExplorer\Middleware\Selector;

use Nyrados\WebExplorer\Middleware\DefaultMiddleware;
use Nyrados\WebExplorer\Middleware\DirectoryList;
use Nyrados\WebExplorer\Middleware\FileView;
use Nyrados\WebExplorer\WebExplorer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

class QueryActionSelector implements MiddlewareSelectorInterface
{
    /**
     * @var WebExplorer
    */
    private $we;

    public const MIDDLEWARES = [
        'list' => DirectoryList::class,
        'view' => FileView::class
    ];

    public function __construct(WebExplorer $we)
    {
        $this->we = $we;
    }

    public function selectMiddleware(ServerRequestInterface $request): MiddlewareInterface
    {
        $action = $this->selectName($request);

        if ($action === null || !isset(self::MIDDLEWARES[$action])) {
            return new DefaultMiddleware();
        }

        $middleware = self::MIDDLEWARES[$action];
        
        return new $middleware($this->we);
    }

    protected function selectName(ServerRequestInterface $request): ?string
    {
        $get = $request->getQueryParams();
        if (!isset($get['action'])) {
            return null;
        }

        return $get['action'];
    }
}
