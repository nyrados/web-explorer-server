<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Utils\File\File;

class CreateDir extends AbstractMiddleware
{
    public function run(File $file, array $params = [])
    {
        $file->assertNotExistance();
        $file->createDirIfNotExists();

        return [];
    }
}