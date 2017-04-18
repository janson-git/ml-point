<?php

use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/vendor/autoload.php';

$data = new \app\DataFrameCsv(__DIR__ . '/data/crx.data', 15, false);

