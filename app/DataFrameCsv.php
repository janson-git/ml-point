<?php declare(strict_types=1);

namespace app;

class DataFrameCsv extends \Phpml\Dataset\CsvDataset
{
    public function shape()
    {
        return [count($this->samples), count($this->columnNames)];
    }

    public function head(int $count = 5)
    {
        return array_slice($this->samples, 0, $count);
    }

    public function tail(int $count = 5)
    {
        return array_slice($this->samples, -$count);
    }
}