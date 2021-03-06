<?php

namespace app;

use DataSet;
use Phpml\Dataset\CsvDataset;
use Phpml\Math\Statistic\Correlation;
use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class DataFrameCsv extends BaseObject
{
    /** @var OutputInterface */
    protected $output;
    /** @var DataSet  */
    protected $dataSet;

    protected $numericColumns  = [];
    protected $categoryColumns = [];

    public function __construct(string $filepath, int $features, bool $headingRow = true)
    {
        $this->dataSet = new CsvDataset($filepath, $features, $headingRow);
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function shape()
    {
        $this->output->writeln([count($this->dataSet->getSamples()), count($this->dataSet->getColumnNames())]);
    }

    public function head(int $count = 5)
    {
        $samples = array_slice($this->dataSet->getSamples(), 0, $count, true);
        $targets = array_slice($this->dataSet->getTargets(), 0, $count);
        foreach ($targets as $key => $val) {
            $samples[$key][] = $val;
        }
        $this->renderTable($samples, $this->dataSet->getColumnNames());
    }

    public function tail(int $count = 5)
    {
        $samples = array_slice($this->dataSet->getSamples(), -$count, null, true);
        $targets = array_slice($this->dataSet->getTargets(), -$count, null, true);
        foreach ($targets as $key => $val) {
            $samples[$key][] = $val;
        }
        $this->renderTable($samples, $this->dataSet->getColumnNames());
    }

    public function setColumnName(int $index, string $name)
    {
        $this->dataSet->setColumnName($index, $name);
    }

    public function at(int $row, int $column)
    {
        return $this->dataSet->at($row, $column);
    }

    public function describe($isCategorical = false)
    {
        /**
         * С помощью метода describe() получим некоторую сводную информацию по всей таблице.
         * По умолчанию будет выдана информация только для количественных признаков.
         * Это общее их количество (count), среднее значение (mean), стандартное отклонение (std),
         * минимальное (min), максимальное (max) значения, медиана (50%) и значения нижнего (25%)
         * и верхнего (75%) квартилей
            count 	678.000000 	690.000000 	690.000000 	690.00000 	677.000000 	690.000000
            mean 	31.568171 	4.758725 	2.223406 	2.40000 	184.014771 	1017.385507
            std 	11.957862 	4.978163 	3.346513 	4.86294 	173.806768 	5210.102598
            min 	13.750000 	0.000000 	0.000000 	0.00000 	0.000000 	0.000000
            25% 	22.602500 	1.000000 	0.165000 	0.00000 	75.000000 	0.000000
            50% 	28.460000 	2.750000 	1.000000 	0.00000 	160.000000 	5.000000
            75% 	38.230000 	7.207500 	2.625000 	3.00000 	276.000000 	395.500000
            max 	80.250000 	28.000000 	28.500000 	67.00000 	2000.000000 	100000.000000
         */
        $this->numericColumns = [];
        $this->categoryColumns = [];
        if ($isCategorical) {
            $describe = [
                'count'  => [],
                'unique' => [],
                'top'    => [],
                'freq'   => [],
            ];
        } else {
            $describe = [
                'count' => [],
                'mean'  => [],
                'std'   => [],
                'min'   => [],
                '25%'   => [],
                '50%'   => [],
                '75%'   => [],
                'max'   => [],
            ];
        }

        $columns = count($this->dataSet->getColumnNames());
        $totalRows = count($this->dataSet->getSamples());
        for ($i = 0; $i < $columns; $i++) {
            $isLastIteration = ($columns === ($i+1));
            if ($isLastIteration) {
                $vals = $this->dataSet->getTargets();
            } else {
                $vals = array_column($this->dataSet->getSamples(), $i);
            }
            $counters = array_count_values($vals);

            $valuesCount = $totalRows;
            if (isset($counters['?'])) {
                $valuesCount -= $counters['?'];
                unset($counters['?']);
                $vals = array_filter($vals, function($item) {
                    return $item !== '?';
                });
            }
            $isCategoricalColumn = null;
            foreach ($counters as $key => $val) {
                if (is_numeric($key)) {
                    $isCategoricalColumn = false;
                } else {
                    $isCategoricalColumn = true;
                }
            }
            if ($isLastIteration) {
                $isCategoricalColumn ? $this->categoryColumns[] = 'class' : $this->numericColumns[] = 'class';
            } else {
                $isCategoricalColumn ? $this->categoryColumns[] = $i : $this->numericColumns[] = $i;
            }

            // use only needed columns
            if ( ($isCategorical && !$isCategoricalColumn) || (!$isCategorical && $isCategoricalColumn) ) {
                continue;
            }

            if ($isCategorical) {
                $columnDescribe = $this->countCategoricalDescribeForValues($vals, $valuesCount);
            } else {
                $columnDescribe = $this->countNumericalDescribeForValues($vals, $valuesCount);
            }
            // fill describe array with current column values
            foreach ($describe as $key => &$values) {
                if (isset($columnDescribe[$key])) {
                    $values[$i] = $columnDescribe[$key];
                }
            }
            unset($values);
        }

        // add row name to display it
        foreach ($describe as $key => &$arr) {
            array_unshift($arr, $key);
        }
        unset($arr);
        $columnNames = $isCategorical ? $this->categoryColumns : $this->numericColumns;

        array_unshift($columnNames, '');
        $this->renderTable($describe, $columnNames);
    }


    protected function countCategoricalDescribeForValues($vals, $valuesCount)
    {
        // В таблице для каждого категориального признака приведено общее число заполненных ячеек (count),
        // количество значений, которые принимает данный признак (unique),
        // самое популярное (часто встречающееся) значение этого признака (top) и количество объектов,
        // в которых встречается самое частое значение данного признака (freq).

        $frequencies = array_count_values($vals);
        arsort($frequencies, SORT_NUMERIC);

        $describe['count']  = (int)$valuesCount;
        $describe['unique'] = (int)count($frequencies);
        $describe['top']    = key($frequencies);
        $describe['freq']   = (int)current($frequencies);

        return $describe;
    }


    protected function countNumericalDescribeForValues($vals, $valuesCount)
    {
        sort($vals);
        $count = count($vals);

        $topPosition = 0.75 * ($count + 1);
        $quartileTop = is_float($topPosition)
            ? (($vals[ (int)floor($topPosition) ] + $vals[ (int)ceil($topPosition) ]) / 2)
            : $vals[ (int)$topPosition ];

        $bottomPosition = 0.25 * ($count + 1);
        $quartileBottom = is_float($bottomPosition)
            ? (($vals[ (int)floor($bottomPosition) ] + $vals[ (int)ceil($bottomPosition) ]) / 2)
            : $vals[ (int)$bottomPosition ];

        $describe['count'] = $this->numberFormat($valuesCount);
        $describe['mean']  = $this->numberFormat(Mean::arithmetic($vals));
        $describe['std']   = $this->numberFormat(StandardDeviation::population($vals));
        $describe['min']   = $this->numberFormat($vals[0]);
        $describe['25%']   = $this->numberFormat($quartileBottom);
        $describe['50%']   = $this->numberFormat(Mean::median($vals));
        $describe['75%']   = $this->numberFormat($quartileTop);
        $describe['max']   = $this->numberFormat(max($vals));

        return $describe;
    }

    public function numericColumns()
    {
        $this->output->writeln(implode(',', $this->numericColumns));
    }

    public function categoryColumns()
    {
        $this->output->writeln(implode(',', $this->categoryColumns));
    }

    /** PROTECTED *****************************************************/

    protected function renderTable($data, $colNames = null)
    {
        $table = new Table($this->output);
        if ($colNames !== null) {
            $table->setHeaders($colNames);
        }
        $table->addRows($data);
        $table->render();
    }

    protected function numberFormat(float $value, int $decimals = 6)
    {
        return number_format($value, $decimals, '.', '');
    }


    protected $correlationColumns = [];

    /**
     * Returns correlation matrix for numeric columns
     * @throws \Phpml\Exception\InvalidArgumentException
     */
    public function corr()
    {
        $matrix = [];

        $columns = count($this->dataSet->getColumnNames());
        for ($i = 0; $i < $columns; $i++) {
            $vals = array_column($this->dataSet->getSamples(), $i);
            $counters = array_count_values($vals);

            $isNumericColumn = null;
            foreach ($counters as $key => $val) {
                if (is_numeric($key)) {
                    $isNumericColumn = true;
                    break;
                }
            }

            // only numeric columns can be used for matrix
            if (!$isNumericColumn) {
                continue;
            }
            $this->correlationColumns[$i] = $vals;
        }

        $count = count($this->correlationColumns);
        foreach ($this->correlationColumns as $i => $columnValuesX) {
            foreach ($this->correlationColumns as $j => $columnValuesY) {
                if ($j === $i) {
                    $matrix[$i][$j] = number_format(1, 6);
                    continue;
                }
                // calculate matrix coefficient for position
                $regressionValue = Correlation::pearson($columnValuesX, $columnValuesY);
                if ($regressionValue !== null) {
                    $matrix[ $i ][ $j ] = $regressionValue;
                }
            }

        }

        $this->renderTable($matrix);
    }

}