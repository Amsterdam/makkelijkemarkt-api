<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\TarievenplanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FTRemoveTarievenplannenCommand extends Command
{
    protected static $defaultName = 'ft:tarievenplannen:flush';

    private TarievenplanRepository $tarievenplanRepository;

    private EntityManagerInterface $em;

    public function __construct(
        TarievenplanRepository $tarievenplanRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->tarievenplanRepository = $tarievenplanRepository;
        $this->em = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Removes all tarievenplannen (from flex tarieven project) and tarieven entries from the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tarievenplannen = $this->tarievenplanRepository->findAll();

        $i = 0;

        // delete all tarievenplannen and tarief entries in the database
        foreach ($tarievenplannen as $tarievenplan) {
            $this->em->remove($tarievenplan);
            ++$i;
        }

        $this->em->flush();

        $io->success("Removed $i tarievenplans");

        return 0;
    }
}
