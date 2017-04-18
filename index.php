<?php declare(strict_types=1);

use app\DataFrameCsv;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application();
$app->setAutoExit(false);

// ... register commands
$app->setDefaultCommand('base');

while (!$app->isAutoExitEnabled()) {
    $result = $app->run();
}


//
//$data = new DataFrameCsv(__DIR__ . '/data/crx.data', 15);
//var_dump($data->shape());
//
//var_dump($data->head());