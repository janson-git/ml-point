<?php

class DataSet extends \Phpml\Dataset\CsvDataset
{
    public function setColumnName(int $index, string $name)
    {
        $this->columnNames[$index] = $name;
    }

    public function at(int $row, int $column)
    {
        return isset($this->samples[$row])
            ? ($this->samples[$row][$column] ?? null)
            : null;
    }
}