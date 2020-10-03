<?php

namespace Nyrados\WebExplorer\Middleware;

use Nyrados\Utils\File\File;

class Rename extends AbstractMiddleware
{
    protected static $params = ['to'];

    public function run(File $file, array $params = [])
    {        

        $dest = $this->getAbsoluteFile($params['to']);
        $dest->assertNotExistance();
        
        $file->rename($dest->getPath());

        return [];
    }
}