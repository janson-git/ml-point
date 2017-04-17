<?php

namespace app\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BatchProcessCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('batch:process')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of items to process')
            ->addOption('no-cleanup', null, InputOption::VALUE_NONE)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>I am going to do something very useful with argument value: {$input->getArgument('type')}</info>");
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $defaultType = 1;
        $question = new Question(
            "<comment>1</comment>: Messages\n",
            "<comment>2</comment>: Jobs\n",
            "<question>Choose a type:</question> [<comment>$defaultType</comment>] ",
            $defaultType
        );
        $question->setValidator(
            function($typeInput) {
                if (!in_array($typeInput, array(1, 2))) {
                    throw new \InvalidArgumentException('Invalid type');
                }
                return $typeInput;
            }
        )->setMaxAttempts(3);

        $type = $this->getHelper('question')->ask($input, $output, $question);

        $input->setArgument('type', $type);
    }
}