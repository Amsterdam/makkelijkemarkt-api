<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Factuur;
use App\Repository\FactuurRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FactuurInfoCommand extends Command
{
    protected static $defaultName = 'app:factuur:info';

    /** @var FactuurRepository */
    private $factuurRepository;

    public function __construct(FactuurRepository $factuurRepository)
    {
        $this->factuurRepository = $factuurRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates invoice info');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Factuur[] $facturen */
        $facturen = $this->factuurRepository->findAll();

        $totalen = [];
        $i = 0;
        $io->note('proccess:');

        /** @var Factuur $factuur */
        foreach ($facturen as $factuur) {
            /** @var ?int $totaal */
            $totaal = $factuur->getTotaal();

            if (0 === $totaal) {
                continue;
            }

            $totalen[] = $totaal;

            $io->text((string) ++$i);

            if ($i > 20000) {
                break;
            }
        }

        $avg = array_sum($totalen) / count($totalen);

        $io->success('average '.$avg);

        return 0;
    }
}
