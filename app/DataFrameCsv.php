<?php declare(strict_types=1);

namespace app;

use Phpml\Math\Statistic\Mean;
use Phpml\Math\Statistic\StandardDeviation;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class DataFrameCsv extends \Phpml\Dataset\CsvDataset
{
    /** @var OutputInterface */
    protected $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function shape()
    {
        $this->output->writeln([count($this->samples), count($this->columnNames)]);
    }

    public function head(int $count = 5)
    {
        $samples = array_slice($this->samples, 0, $count, true);
        $targets = array_slice($this->targets, 0, $count);
        foreach ($targets as $key => $val) {
            array_push($samples[$key], $val);
        }
        $this->renderTable($samples, $this->getColumnNames());
    }

    public function tail(int $count = 5)
    {
        $samples = array_slice($this->samples, -$count, null, true);
        $targets = array_slice($this->targets, -$count, null, true);
        foreach ($targets as $key => $val) {
            array_push($samples[$key], $val);
        }
        $this->renderTable($samples, $this->getColumnNames());
    }

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

    public function describe()
    {
        /** TODO
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
        $numericColumns = [];
        $categoryColumns = [];
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

        $columns = count($this->columnNames);
        $totalRows = count($this->samples);
        for ($i = 0; $i < $columns; $i++) {
            $vals = array_column($this->samples, $i);
            $counters = array_count_values($vals);

            $valuesCount = $totalRows;
            if (isset($counters['?'])) {
                $valuesCount -= $counters['?'];
                unset($counters['?']);
            }
            $isNumeric = null;
            foreach ($counters as $key => $val) {
                if (is_numeric($key) && $isNumeric !== false) {
                    $isNumeric = true;
                } else {
                    $isNumeric = false;
                }
            }

            $isNumeric ? $numericColumns[] = $i : $categoryColumns[] = $i;

            // TODO: пока работаем только с количественными признаками
            if (!$isNumeric) {
                continue;
            }

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

            $describe['count'][$i] = array_sum($counters);
            $describe['mean'][$i]  = Mean::arithmetic($vals);
            $describe['std'][$i]   = StandardDeviation::population($vals);
            $describe['min'][$i]   = $vals[0];
            $describe['25%'][$i]   = $quartileBottom;
            $describe['50%'][$i]   = Mean::median($vals);
            $describe['75%'][$i]   = $quartileTop;
            $describe['max'][$i]   = max($vals);
        }

        $this->renderTable($describe);
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
}