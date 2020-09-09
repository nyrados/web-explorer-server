<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Utils\File\File;

class Delete extends AbstractMiddleware
{
    public function run(File $file, array $params = [])
    {
        if ($file->exists()) {
            $file->delete();
        }
    }
}