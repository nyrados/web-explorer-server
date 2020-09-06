<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Utils\File\Exception\FileAlreadyExistsException;
use Nyrados\Utils\File\File;
use Nyrados\Utils\File\Path;
use Nyrados\WebExplorer\Entity\FileEntity;

class DirectoryList extends AbstractMiddleware
{
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

    public function buildEntity(File $file, string $location)
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

    private function buildFileEntity(File $file)
    {
        $stat = $file->stat();
        $data = [
            'type' => 'file',
            'size' => $stat['size']
        ];

        return $data;
    }

    private function buildDirectoryEntity(File $file)
    {
        return [
            'type' => 'dir'
        ];
    }

    /*
    public function run(string $directory): void
    {
        $directory = $this->getAbsoluteFile($directory);
        $targetPath = substr($directory->getPath(), strlen($this->file->getPath()));

        if ($targetPath === '') {
            $targetPath = '/';
        }

        $target = $directory->withPath($targetPath);

        $rs = [];

        foreach ($directory->getChildren() as $child) {
            var_dump($target->withPath($child->getName()));

            $rs[] = (new FileEntity($child, $target->withPath($child->getName())))->jsonSerialize();
        }

        var_dump($rs);
    }
    */
}
