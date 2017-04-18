<?php declare(strict_types=1);

namespace app;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class DataFrameCsv extends \Phpml\Dataset\CsvDataset
{
    public function shape()
    {
        echo new DataFrameShape(count($this->samples), count($this->columnNames));
    }

    public function head(int $count = 5)
    {
        $this->renderTable(array_slice($this->samples, 0, $count));
    }

    public function tail(int $count = 5)
    {
        $this->renderTable(array_slice($this->samples, -$count));
    }

    protected function renderTable($data)
    {
        $output = new ConsoleOutput();
        $table = new Table($output);
        $table->setHeaders($this->getColumnNames());
        $table->addRows($data);
        $table->render();
        return;
    }
}