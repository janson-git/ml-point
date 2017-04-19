<?php declare(strict_types=1);

namespace app;

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
        $this->renderTable($samples);
    }

    public function tail(int $count = 5)
    {
        $samples = array_slice($this->samples, -$count, null, true);
        $targets = array_slice($this->targets, -$count, null, true);
        foreach ($targets as $key => $val) {
            array_push($samples[$key], $val);
        }
        $this->renderTable($samples);
    }

    public function setColumnNames(array $columnNames)
    {
        $this->columnNames = $columnNames;
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
         * минимальное (min), макcимальное (max) значения, медиана (50%) и значения нижнего (25%)
         * и верхнего (75%) квартилей
         */
    }

    /** PROTECTED *****************************************************/

    protected function renderTable($data)
    {
        $table = new Table($this->output);
        $table->setHeaders($this->getColumnNames());
        $table->addRows($data);
        $table->render();
        return;
    }
}