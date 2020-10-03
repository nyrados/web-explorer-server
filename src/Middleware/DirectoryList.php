<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Utils\File\File;
use Nyrados\Utils\File\Path;

class DirectoryList extends AbstractMiddleware
{
    protected static $method = 'POST';
    
    public function run(File $file, array $params = [])
    {
        $file->assertIsDirectory();
        $rs = [];

        foreach ($file->getChildren() as $child) {
            if ($child->isReadable()) {
                $rs[] = $this->buildEntity($child, $params['file']);
            }
        }

        return $rs;
    }

    public function buildEntity(File $file, string $location): array
    {
        return array_merge(
            [
                'name' => $file->getName(),
                'path' => (new Path($location))->asAbsolute()->withPath($file->getName())->getPath(),
                'modified' => filemtime($file->getWrapper())
            ],
            $file->isDir()
                ? $this->buildDirectoryEntity($file)
                : $this->buildFileEntity($file)
        );
    }

    private function buildFileEntity(File $file): array
    {
        $stat = $file->stat();
        $split = explode('.', $file->getName());

        $data = [
            'mime' => $file->getMimeType(),
            'type' => 'file',
            'size' => $stat['size'],
            'extension' => array_pop($split)
        ];

        return $data;
    }

    private function buildDirectoryEntity(File $file): array
    {
        return [
            'type' => 'dir'
        ];
    }
}
