<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Dagvergunning;
use App\Entity\Factuur;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Entity\Product;
use App\Repository\FactuurRepository;
use App\Repository\MarktRepository;
use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FactuurReportCommand extends Command
{
    protected static $defaultName = 'app:factuur:report';

    /** @var FactuurRepository $factuurRepository */
    private $factuurRepository;

    /** @var MarktRepository $marktRepository */
    private $marktRepository;

    public function __construct(FactuurRepository $factuurRepository, MarktRepository $marktRepository)
    {
        $this->factuurRepository = $factuurRepository;
        $this->marktRepository = $marktRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generates factuur json data')
            ->addArgument('startdate', InputArgument::REQUIRED, 'Start date yyyy-mm-dd')
            ->addArgument('enddate', InputArgument::REQUIRED, 'End date yyyy-mm-dd')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $startDate */
        $startDate = $input->getArgument('startdate');

        /** @var string $endDate */
        $endDate = $input->getArgument('enddate');

        $io->note(sprintf('[arguments] startDate: %s, endDate %s', $startDate, $endDate));

        /** @var resource $out */
        $out = fopen('php://output', 'w');
        fputcsv($out, [
            'markt',
            'dagvergunningId',
            'koopmanErkenningsnummer',
            'dag',
            'voorletters',
            'achternaam',
            'productNaam',
            'productAantal',
            'productBedrag',
            'btwPerProduct',
            'totaalBtw',
            'totaalExclusief', ]);

        gc_enable();

        /** @var DateTime $startDate */
        $startDate = new DateTime($startDate);

        /** @var DateTime $endDate */
        $endDate = new DateTime($endDate);

        while ($startDate <= $endDate) {
            /** @var Markt[] $markten */
            $markten = $this->marktRepository->findAllSorted();

            foreach ($markten as $markt) {
                /*** @var Factuur[] $facturen */
                $facturen = $this->factuurRepository->findAllByMarktAndRange(
                    $markt,
                    $startDate->format('Y-m-d'),
                    $startDate->format('Y-m-d')
                );

                foreach ($facturen as $factuur) {
                    /** @var Dagvergunning $dagvergunning */
                    $dagvergunning = $factuur->getDagvergunning();

                    /** @var Koopman $koopman */
                    $koopman = $dagvergunning->getKoopman();

                    /** @var Product[] $producten */
                    $producten = $factuur->getProducten();

                    /** @var Product $product */
                    foreach ($producten as $product) {
                        fputcsv($out, [
                            $markt->getNaam(),
                            $dagvergunning->getId(),
                            $koopman->getErkenningsnummer(),
                            $dagvergunning->getDag()->format('d-m-Y'),
                            $koopman->getVoorletters(),
                            $koopman->getAchternaam(),
                            $product->getNaam(),
                            $product->getAantal(),
                            $product->getBedrag(),
                            $product->getBtwPerProduct(),
                            $product->getBtwTotaal(),
                            $product->getTotaal(),
                        ]);
                    }
                }
            }

            gc_collect_cycles();
            $startDate->modify('+1 day');
        }

        fclose($out);

        $io->success('done');

        return 0;
    }
}
