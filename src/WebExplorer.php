<?php
namespace Nyrados\WebExplorer;

use Nyrados\Utils\File\File;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyrados\WebExplorer\Middleware\DirectoryList;
use Nyrados\Utils\File\Exception\FileNotFoundException;
use Nyrados\Utils\File\Exception\FileNotReadableException;
use Nyrados\Utils\File\Exception\FileNotWriteableException;
use Nyrados\Utils\File\Exception\FileAlreadyExistsException;
use Nyrados\WebExplorer\Middleware\Selector\QueryActionSelector;
use Nyrados\WebExplorer\Middleware\Selector\MiddlewareSelectorInterface;

class WebExplorer implements MiddlewareInterface
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var MiddlewareSelectorInterface
     */
    private $selector;

    public const
        MIDDLEWARES = [
            'list' => DirectoryList::class,
        ],
        EXCEPTION = [
            FileAlreadyExistsException::class => 'file_already_exists',
            FileNotFoundException::class => 'file_not_found',
            FileNotReadableException::class => 'file_not_readable',
            FileNotWriteableException::class => 'file_not_writeable'
        ];

    public function __construct(string $target)
    {
        $this->file = new File($target);
        $this->selector = new QueryActionSelector($this);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->selector->selectMiddleware($request)
            ->process($request, $handler);
    }

    public function asTarget(File $file): File
    {
        return $file;
    }

    public function getBaseFile()
    {
        return $this->file;
    }
}
