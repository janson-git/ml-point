<?php

namespace app;


class DataFrameShape
{
    protected $samplesCount;
    protected $columnsCount;

    public function __construct(int $samplesCount, int $columnsCount)
    {
        $this->samplesCount = $samplesCount;
        $this->columnsCount = $columnsCount;
    }

    public function __toString()
    {
        return json_encode(['samples' => $this->samplesCount, 'columns' => $this->columnsCount]);
    }
}