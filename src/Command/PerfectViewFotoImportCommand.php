<?php

declare(strict_types=1);

namespace App\Command;

use App\Process\PerfectViewKoopmanFotoImport;
use App\Utils\CsvIterator;
use App\Utils\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PerfectViewFotoImportCommand extends Command
{
    protected $process;

    public function __construct(PerfectViewKoopmanFotoImport $process)
    {
        parent::__construct();
        $this->process = $process;
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('makkelijkemarkt:import:perfectview:foto');
        $this->setDescription('Importeert een CSV bestand en foto map uit PerfectView');
        $this->addArgument('file', InputArgument::REQUIRED, 'CSV bestand met koopman informatie');
        $this->addArgument('directory', InputArgument::REQUIRED, 'Map met foto\'s');
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger();
        $logger->addOutput($output);

        $this->process->setLogger($logger);

        $file = $input->getArgument('file');
        $dir = $input->getArgument('directory');
        $logger->info('PerfectView Koopman Foto Import');
        $logger->info('Start date/time', ['datetime' => date('c')]);
        $logger->info('File', ['file' => $file]);
        $content = new CsvIterator($file);
        $this->process->execute($content, $dir);
        $logger->info('Import done', ['datetime' => date('c')]);

        return 0;
    }
}
