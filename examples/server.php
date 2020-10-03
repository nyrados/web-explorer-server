<?php

use Nyrados\WebExplorer\WebExplorer;

require '../vendor/autoload.php';

$webExplorer = new WebExplorer(__DIR__ . '/..');
$webExplorer->run();