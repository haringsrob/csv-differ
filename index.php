<?php

include_once __DIR__ . '/vendor/autoload.php';

if (!ini_get('auto_detect_line_endings')) {
    ini_set('auto_detect_line_endings', '1');
}

use Csvdiffer\Commands\CompareAndUpdate;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new CompareAndUpdate());
$application->run();


