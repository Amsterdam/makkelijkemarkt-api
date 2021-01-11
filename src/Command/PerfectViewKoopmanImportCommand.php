<?php
declare(strict_types=1);

namespace App\Command;

use App\Process\PerfectViewKoopmanImport;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Utils\Logger;
use App\Utils\CsvIterator;
use Symfony\Component\Console\Command\Command;

class PerfectViewKoopmanImportCommand extends Command
{
    protected $process;

    public function __construct(PerfectViewKoopmanImport $process)
    {
        parent::__construct();
        $this->process = $process;
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('makkelijkemarkt:import:perfectview:koopman');
        $this->setDescription('Importeert een CSV bestand uit PerfectView met koopman informatie');
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV bestand met koopman informatie');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger();
        $logger->addOutput($output);

        $this->process->setLogger($logger);

        $file = $input->getArgument('file');
        $logger->info('PerfectView Koopman Import');
        $logger->info('Start date/time', ['datetime' => date('c')]);
        $logger->info('File', ['file' => $file]);
        $content = new CsvIterator($file);
        $this->process->execute($content);
        $logger->info('Import done', ['datetime' => date('c')]);

        return 0;
    }
}