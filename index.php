<?php declare(strict_types=1);

use app\command\BatchProcessCommand;
use app\DataFrameCsv;

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$app = new Application();

// ... register commands
$app->add(new BatchProcessCommand());

$app->run();


//
//$data = new DataFrameCsv(__DIR__ . '/data/crx.data', 15);
//var_dump($data->shape());
//
//var_dump($data->head());