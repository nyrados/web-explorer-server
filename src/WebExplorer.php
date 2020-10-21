<?php
namespace Nyrados\WebExplorer;

use Nyrados\Utils\File\File;
use GuzzleHttp\Psr7\ServerRequest;
use Nyrados\Http\Utils\ResponseDumper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Nyrados\WebExplorer\Middleware\DirectoryList;
use Nyrados\Http\Utils\Factory\Guzzle\ResponseFactory;
use Nyrados\Http\Utils\IncomingRequestFactoryInterface;
use Nyrados\Utils\File\Exception\FileNotFoundException;
use Nyrados\Utils\File\Exception\FileNotReadableException;
use Nyrados\Utils\File\Exception\FileNotWriteableException;
use Nyrados\Http\Utils\Middleware\InvokeableMiddlewareTrait;
use Nyrados\Utils\File\Exception\FileAlreadyExistsException;
use Nyrados\Http\Utils\Factory\Guzzle\IncomingRequestFactory;
use Nyrados\WebExplorer\Middleware\Selector\QueryActionSelector;
use Nyrados\WebExplorer\Middleware\Selector\MiddlewareSelectorInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class WebExplorer implements MiddlewareInterface
{
    use InvokeableMiddlewareTrait;

    /**
     * @var File
     */
    private $file;

    /**
     * @var MiddlewareSelectorInterface
     */
    private $selector;

    /**
     * @var IncomingRequestFactoryInterface
     */
    private $incomingRequestFactory;

    /** 
     * @var ResponseFactoryInterface 
     */
    private $responseFactory;

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
        $this->responseFactory = new ResponseFactory();
        $this->incomingRequestFactory = new IncomingRequestFactory();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->selector->selectMiddleware($request)
            ->process($request, $handler);
    }

    public function run(ServerRequestInterface $request = null): void
    {
        $request = $request ?? $request = $this->incomingRequestFactory->createIncomingRequest();
        $response = $this($request, $this->responseFactory->createResponse());

        $dumper = new ResponseDumper($response);
        $dumper->dump();
    }
    
    public function getBaseFile()
    {
        return $this->file;
    }

    public function setMiddlewareSelector(MiddlewareSelectorInterface $selector)
    {
        $this->selector = $selector;
    }

    public function setResponseFactory(ResponseFactoryInterface $factory)
    {
        $this->responseFactory = $factory;
    }
}
