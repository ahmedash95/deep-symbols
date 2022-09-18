<?php

require __DIR__.'/vendor/autoload.php';

use DeepSymbols\Indexer;

$argv = $_SERVER['argv'];

$parser = new \DeepSymbols\Parser();
$output = $parser->parse($argv[1], $argv[2]);

foreach($output as $item) {
    echo $item['path'] . PHP_EOL;
}