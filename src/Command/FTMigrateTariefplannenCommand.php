<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Tarief;
use App\Entity\Tariefplan;
use App\Entity\Tarievenplan;
use App\Repository\TariefplanRepository;
use App\Repository\TariefSoortRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class FTMigrateTariefplannenCommand extends Command
{
    protected static $defaultName = 'flextarieven:tariefplannen:migrate';

    private TariefplanRepository $tariefplanRepository;

    private TariefsoortRepository $tariefSoortRepository;

    private EntityManagerInterface $em;

    private $tariefSoortenAndGetters;

    public function __construct(
        TariefplanRepository $tariefplanRepository,
        TariefSoortRepository $tariefSoortRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->tariefplanRepository = $tariefplanRepository;
        $this->tariefSoortRepository = $tariefSoortRepository;
        $this->em = $entityManager;
        $this->tariefSoortenAndGetters = json_decode(file_get_contents(getcwd().'/src/DataFixtures/fixtures/tariefSoortenAndTariefplanGetters.json'), true);
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Migrates everything from the tariefplan, concreetplan and lineaire plannen tables to tarievenplan and tarieven');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tariefplannen = $this->tariefplanRepository->findAll();

        $i = 0;

        foreach ($tariefplannen as $tariefplan) {
            $lineairPlan = $tariefplan->getLineairplan();
            $concreetPlan = $tariefplan->getConcreetplan();
            try {
                if (isset($lineairPlan)) {
                    $this->handlePlan($tariefplan, $lineairPlan, 'lineair');
                    ++$i;
                }

                if (isset($concreetPlan)) {
                    $this->handlePlan($tariefplan, $concreetPlan, 'concreet');
                    ++$i;
                }
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }

        $io->success("Added $i new Tarievenplans");

        return 0;
    }

    private function handlePlan(Tariefplan $tariefplan, $oldPlan, string $type)
    {
        $planGetters = $this->tariefSoortenAndGetters[$type];

        /** @var Tarievenplan */
        $newLineairPlan = (new Tarievenplan())
            ->setType($type)
            ->setMarkt($tariefplan->getMarkt())
            ->setName($tariefplan->getNaam())
            ->setDateFrom($tariefplan->getGeldigVanaf())
            ->setVariant('standard')
            ->setIgnoreVastePlaats(false)
            ->setMonday(false)
            ->setTuesday(false)
            ->setWednesday(false)
            ->setThursday(false)
            ->setFriday(false)
            ->setSaturday(false)
            ->setSunday(false)
            ->setDeleted(false)
            ->setDateUntil(null);

        $tarieven = [];

        foreach ($planGetters as $tariefSoortLabel => $getter) {
            $tariefWaarde = $oldPlan->$getter();

            $tariefSoort = $this->tariefSoortRepository->findOneBy([
                'label' => $tariefSoortLabel,
                'tariefType' => $type,
            ]);

            if ($tariefWaarde < 0.01) {
                continue;
            }

            $tarief = (new Tarief())
                ->setTarief($tariefWaarde)
                ->setTariefSoort($tariefSoort)
                ->setTarievenplan($newLineairPlan);

            array_push($tarieven, $tarief);

            $this->em->persist($tarief);
        }

        $this->em->persist($newLineairPlan);
        $this->em->flush();
    }
}
