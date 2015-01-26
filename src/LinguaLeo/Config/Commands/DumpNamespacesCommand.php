<?php
namespace LinguaLeo\Config\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use LinguaLeo\Config\DataReader;
use LinguaLeo\Config\DataDumper;

class DumpNamespacesCommand extends Command
{
    /**
     * @param array $schema
     * @return array
     */
    private function getDefaultPath(array $schema)
    {
        $path = [];
        foreach ($schema as $name) {
            $path[$name] = '*';
        }
        return $path;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('cfg:dump-namespaces')
            ->setDescription('Dump Namespaces')
            ->addOption(
                'source-path',
                null,
                InputOption::VALUE_REQUIRED,
                'The folder that stores folders of namespaces'
            )
            ->addOption(
                'output-file',
                null,
                InputOption::VALUE_REQUIRED,
                'In this file will be saved cache of namespaces'
            )
            ->addOption(
                'schema',
                null,
                InputOption::VALUE_REQUIRED,
                'Schema elements, delimiter ","'
            );
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourcePath = $input->getOption('source-path');
        $outputFile = $input->getOption('output-file');
        $schema = explode(',', $input->getOption('schema'));
        $defaultPath = $this->getDefaultPath($schema);
        $dataReader = new DataReader($schema, $defaultPath);
        $data = $dataReader->getNamespacesData($sourcePath);
        if (DataDumper::dumpData($outputFile, $data)) {
            $output->writeln('Dump SUCCESS');
            return 0;
        }
        $output->writeln('Dump FAILED');
        return 1;
    }
}
