<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Http\Utils\Middleware\InvokeableMiddlewareTrait;
use Nyrados\Utils\File\Exception\FileException;
use Nyrados\WebExplorer\WebExplorer;
use Nyrados\Utils\File\File;
use Nyrados\Utils\File\Path;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

abstract class AbstractMiddleware implements MiddlewareInterface
{
    use InvokeableMiddlewareTrait;

    protected static $params = [];
    protected static $method = 'POST';

    /**
     * @var File
     */
    protected $file;

    /**
     * @var WebExplorer
     */
    protected $we;

    /**
     * @var ResponseInterface
     */
    protected $response;


    public function __construct(WebExplorer $explorer)
    {
        $this->file = $explorer->getBaseFile();
        $this->we = $explorer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        $this->response = $handler->handle($request);


        $body = $this->response->getBody();
        if (!$body->isWritable()) {
            throw new RuntimeException('WebExplorer requires a writeable Body');
        }

        $body->rewind();

        if (strtoupper($request->getMethod()) !== static::$method) {
            return $this->error($this->response, ['error' => 'invalid_method'])
                ->withStatus(405)
                ->withHeader('Allow', static::$method);
        }

        $param = static::$method === 'GET'
            ? $request->getQueryParams()
            : $request->getParsedBody()
        ;

        foreach (array_merge(static::$params, ['file']) as $name) {
            if (!isset($param[$name])) {
                return $this->error($this->response, ['error' => 'missing_parameter'])
                    ->withStatus(422);
            }
        }

        $file = $this->getAbsoluteFile($param['file']);

        try {
            return $this->convertRunResponse($this->run($file, $param));
        } catch (FileException $e) {
            return $this->error($this->response, $this->handleFileException($e, $param['file']));
        }
    }

    private function convertRunResponse($value): ResponseInterface
    {
        if ($value instanceof ResponseInterface) {
            return $value;
        }

        if (is_array($value)) {
            $body = $this->response->getBody();
            $body->rewind();
            $body->write(json_encode($value, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            
            return $this->response
                ->withBody($body)
                ->withHeader('Content-Type', 'application/json');
        }

        return $this->response;
    }

    protected function getAbsoluteFile(string $path): File
    {
        $file = new Path(urldecode($path));
    
        return $this->file->withPath($file->asRelative()->getPath());
    }

    private function handleFileException(FileException $e): array
    {
        $target = substr($e->getFilename(), strlen($this->file->getPath()));

        return [
            'error' => isset(WebExplorer::EXCEPTION[get_class($e)])
                ? WebExplorer::EXCEPTION[get_class($e)]
                : 'file_error',
            'message' => str_replace($e->getFilename(), $target, $e->getMessage()),
        ];
    }

    private function error(ResponseInterface $response, array $data): ResponseInterface
    {
        $body = $response->getBody();
        $body->write(json_encode($data, JSON_UNESCAPED_SLASHES));

        return $response
            ->withBody($body)
            ->withStatus(400)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * Run Middleware
     *
     * @param File $file
     * @param array<string> $params
     * @return ResponseInterface|array<mixed>|void
     */
    abstract public function run(File $file, array $params = []);
}
