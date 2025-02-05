<?php

use Symfony\Component\Dotenv\Dotenv;

$projectDir = __DIR__.'/../../';

require $projectDir.'vendor/autoload.php';

echo 'Wait database...'.PHP_EOL;

set_time_limit(10);

(new Dotenv())->load($projectDir.'.env');

$host = 'postgres';

while (true) {
    if (@fsockopen($host.':5432')) {
        break;
    }
}
