<?php

namespace Csvdiffer\Commands;

use League\Csv\Reader;
use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompareAndUpdate extends Command
{

    protected function configure()
    {
        $this
            ->setName('diff-csv')
            ->setDescription(
                'Diffs 2 csv documents and removes from the first what is found in the second.'
            )
            ->addOption(
                'source_file', null, InputOption::VALUE_REQUIRED,
                'The source file you want to update'
            )
            ->addOption(
                'source_header', null, InputOption::VALUE_REQUIRED,
                'The header in the source file to use'
            )
            ->addOption(
                'compare_with', null, InputOption::VALUE_REQUIRED,
                'The compare file you want to use to update'
            )
            ->addOption(
                'compare_header', null, InputOption::VALUE_REQUIRED,
                'The header in the compare file to use'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currentDir = getcwd();

        $source_file = $currentDir . '/' . $input->getOption('source_file');
        $source_header = $input->getOption('source_header');
        $compare_file = $currentDir . '/' . $input->getOption('compare_with');
        $compare_header = $input->getOption('compare_header');

        if (!$this->checkIfSourceExists(
                $output, $source_file
            ) && !$this->checkIfCompareFileExists($output, $compare_file)) {
            return;
        }

        $existing_eans = $this->getExistingRowValues(
            $compare_file, $compare_header
        );

        // Source file.
        $source_csv = Reader::createFromPath($source_file);
        $source_csv->setDelimiter(';');
        $source_csv->setHeaderOffset(0);

        // New file.
        if (!file_exists($currentDir . '/csv_documents/processed.csv')) {
            touch($currentDir . '/csv_documents/processed.csv');
        }
        $csv = Writer::createFromPath(
            $currentDir . '/csv_documents/processed.csv'
        );
        //insert the header
        $csv->insertOne($source_csv->getHeader());

        // Process.
        $records = $source_csv->getRecords();
        $index = 1;
        $duplicates = 0;
        foreach ($records as $record) {
            if (!empty($record[$source_header])) {
                if (\in_array($record[$source_header], $existing_eans, true)) {
                    $duplicates++;
                } else {
                    $csv->insertOne($record);
                }
            }

            $index++;
        }

        $output->writeln(
            'Found ' . $index . ' ean codes and cleared ' . $duplicates . ' matches'
        );

        $output->writeln($currentDir . '/processed.csv');
    }

    protected function checkIfSourceExists(
        OutputInterface $output,
        $source_file
    ): bool {
        if (!file_exists($source_file)) {
            $output->writeln('Source file could not be found.');
            return false;
        }
        return true;
    }

    protected function checkIfCompareFileExists(
        OutputInterface $output,
        $compare_file
    ): bool {
        if (!file_exists($compare_file)) {
            $output->writeln('Compare file could not be found.');
            return false;
        }
        return true;
    }

    private function getExistingRowValues(
        string $compare_file,
        string $compare_header
    ): array {
        $compare_csv = Reader::createFromPath($compare_file, 'r');
        $compare_csv->setDelimiter(',');
        $compare_csv->setHeaderOffset(0);

        $records = $compare_csv->getRecords();
        $existing_eans = [];
        foreach ($records as $record) {
            if (!empty($record[$compare_header])) {
                $existing_eans[] = $record[$compare_header];
            }
        }

        return $existing_eans;
    }
}
