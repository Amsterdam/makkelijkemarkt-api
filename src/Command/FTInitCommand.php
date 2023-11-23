<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\FeatureFlag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FTInitCommand extends Command
{
    protected static $defaultName = 'flextarieven:init';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Executes multiple commands to migrate data for flexibele tarieven');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $commands = [
            'flextarieven:dagvergunningmapping:seed',
            'flextarieven:tariefsoort:update',
            'flextarieven:tariefplannen:migrate',
            'flextarieven:migrate:dagvergunningmappings',
        ];

        foreach ($commands as $command) {
            try {
                $command = $this->getApplication()->find($command);
                $io->writeln('Executing command: '.$command->getName());
                $command->run(new ArrayInput([]), $output);
            } catch (\Exception $e) {
                $io->error('Error executing command: '.$command->getName());
                $io->error($e->getMessage());

                return COMMAND::FAILURE;
            }

            $io->writeln('Finished command: '.$command->getName());
        }

        $featureFlag = (new FeatureFlag())
            ->setFeature('flexibele-tarieven')
            ->setEnabled(false);

        $this->em->persist($featureFlag);
        $this->em->flush();

        $io->writeln('Added flexibele-tarieven feature flag as disabled.');

        $io->success('Finished executing all commands');

        return COMMAND::SUCCESS;
    }
}
