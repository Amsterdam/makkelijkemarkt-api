<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use App\Repository\DagvergunningRepository;
use App\Service\FactuurService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FactuurRepairCommand extends Command
{
    protected static $defaultName = 'app:factuur:repair';

    /** @var DagvergunningRepository */
    private $dagvergunningRepository;

    /** @var FactuurService */
    private $factuurService;

    public function __construct(DagvergunningRepository $dagvergunningRepository, FactuurService $factuurService)
    {
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->factuurService = $factuurService;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates missing invoices');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var Dagvergunning[] $dagvergunningen */
        $dagvergunningen = $this->dagvergunningRepository->findBy([
                'factuur' => null,
                'doorgehaald' => false,
            ]);

        $datum = new \DateTime('2015-12-30 00:00:00');
        $i = 0;

        foreach ($dagvergunningen as $dagvergunning) {
            if ($datum <= $dagvergunning->getAanmaakDatumtijd()) {
                /** @var float $ts */
                $ts = microtime(true);

                $io->title('Processing id '.$dagvergunning->getId());

                /** @var ?Factuur $factuur */
                $factuur = $this->factuurService->createFactuur($dagvergunning);

                $seconds = microtime(true) - $ts;
                $io->text('Create factuur in '.$seconds.' seconds');

                if (null !== $factuur) {
                    $this->factuurService->saveFactuur($factuur);

                    $seconds = microtime(true) - $ts;
                    $io->text('Save factuur   in '.$seconds.' seconds');
                } else {
                    $io->warning('No Factuur');
                }

                $seconds = microtime(true) - $ts;
                $io->text('Finished       in '.$seconds.' seconds');
                ++$i;
            }
        }

        $io->success('Count '.$i);

        return 0;
    }
}
