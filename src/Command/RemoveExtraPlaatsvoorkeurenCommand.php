<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\PlaatsVoorkeur;
use App\Repository\PlaatsVoorkeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RemoveExtraPlaatsvoorkeurenCommand extends Command
{
    protected static $defaultName = 'app:plaatsvoorkeur:remove_extra';

    /** @var PlaatsVoorkeurRepository */
    private $plaatsvoorkeurRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(PlaatsVoorkeurRepository $plaatsVoorkeurRepository, EntityManagerInterface $entityManager)
    {
        $this->plaatsvoorkeurRepository = $plaatsVoorkeurRepository;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Removes all plaatsvoorkeuren higher than prio 6');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var PlaatsVoorkeur[] $plaatsvoorkeuren */
        $plaatsvoorkeuren = $this->plaatsvoorkeurRepository->findAll();

        try {
            /** @var plaatsvoorkeur $plaatsvoorkeur */
            foreach ($plaatsvoorkeuren as $plaatsvoorkeur) {
                $plaatsen = $plaatsvoorkeur->getPlaatsen();

                if (count($plaatsen) > 6) {
                    $output->writeln(
                        'Found more than 6 plaatsvoorkeuren for ID: '.$plaatsvoorkeur->getId().' Plaatsen: '.implode(',', $plaatsen)
                    );
                }

                $plaatsvoorkeur->setPlaatsen(array_slice($plaatsen, 0, 6));

                $this->entityManager->persist($plaatsvoorkeur);
            }

            $this->entityManager->flush();
        } catch (\Error $err) {
            $output->writeln('[Error] '.$err->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
