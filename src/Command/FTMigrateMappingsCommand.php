<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\DagvergunningMappingRepository;
use App\Repository\MarktRepository;
use App\Repository\TarievenplanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FTMigrateMappingsCommand extends Command
{
    protected static $defaultName = 'flextarieven:migrate:dagvergunningmappings';

    private DagvergunningMappingRepository $dagvergunningMappingRepository;

    private MarktRepository $marktRepository;

    private TarievenplanRepository $tarievenplanRepository;

    private EntityManagerInterface $em;

    public function __construct(
        DagvergunningMappingRepository $dagvergunningMappingRepository,
        MarktRepository $marktRepository,
        TarievenplanRepository $tarievenplanRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->dagvergunningMappingRepository = $dagvergunningMappingRepository;
        $this->marktRepository = $marktRepository;
        $this->tarievenplanRepository = $tarievenplanRepository;
        $this->em = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Migrates everything from the tariefplan, concreetplan and lineaire plannen tables to tarievenplan and tarieven');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $i = 0;
        $dagvergunningMappings = $this->dagvergunningMappingRepository->findAll();
        $markten = $this->marktRepository->findAll();

        foreach ($markten as $markt) {
            $opties = $markt->getAanwezigeOpties();
            $tarievenplan = $this->tarievenplanRepository->findOneBy(['markt' => $markt], ['id' => 'DESC']);

            if ($tarievenplan) {
                foreach ($opties as $optie) {
                    foreach ($dagvergunningMappings as $mapping) {
                        if ($mapping->getDagvergunningKey() === $optie
                            && $mapping->getTariefType() === $tarievenplan->getType()
                        ) {
                            $markt->addDagvergunningMapping($mapping);
                        }
                    }
                }
            }

            $this->em->persist($markt);
            ++$i;
        }

        $this->em->flush();

        $io->success("Added mappings to $i markets");

        return 0;
    }
}
