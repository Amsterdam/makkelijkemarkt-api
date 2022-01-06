<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Dagvergunning;
use App\Repository\KoopmanRepository;
use App\Repository\DagvergunningRepository;
use App\Service\FactuurService;
use App\Service\PdfFactuurService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Utils\Logger;
use Symfony\Component\Console\Command\Command;

class StuurFactuurCommand extends Command
{
    /**
     * @var KoopmanRepository
     */
    private $koopmanRepository;

    /**
     * @var DagvergunningRepository
     */
    private $dagvergunningRepository;

    /**
     * @var PdfFactuurService
     */
    private $pdfFactuurService;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var FactuurService
     */
    private $factuurService;

    public function __construct(KoopmanRepository $koopmanRepository, DagvergunningRepository $dagvergunningRepository, PdfFactuurService $pdfFactuurService, \Swift_Mailer $mailer, FactuurService $factuurService)
    {
        $this->koopmanRepository = $koopmanRepository;
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->pdfFactuurService = $pdfFactuurService;
        $this->mailer = $mailer;
        $this->factuurService = $factuurService;

        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('makkelijkemarkt:factuur:versturen');
        $this->setDescription('Verstuur facturen per mail');
        $this->addArgument('date');
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger();
        $logger->addOutput($output);

        $date = $input->getArgument('date');
        if ($date === null || $date === '') {
            $date = date('Y-m-d');
        }
        $output->writeln($date);

        $koopmannen = $this->koopmanRepository->getWithDagvergunningOnDag(new \DateTime($date));

        foreach ($koopmannen as $koopman) {
            try {
                /** @var Koopman $koopman */
                $output->writeln('Found koopman with id: ' . $koopman->getId() . ' ' . $koopman->getErkenningsnummer());

                if (empty($koopman->getEmail())) {
                    $output->writeln('.. Skip (no e-mail)');
                    continue;
                }
                $dagvergunningen = $this->dagvergunningRepository->search(array(
                    'dag' => $date,
                    'koopmanId' => $koopman->getId(),
                    'doorgehaald' => 0
                ));
                
                $heeftBetaalbaarBedrag = false;
                foreach ($dagvergunningen as $dagvergunning) {
                    /** @var Dagvergunning $dagvergunning */
                    $totaalBedrag = $this->factuurService->getTotaalExclBtw($dagvergunning->getFactuur());
                    $output->writeln('.. Dagvergunning ' . $dagvergunning->getId() . ' / Invoice total ' . $totaalBedrag);
                    if ($totaalBedrag > 0.01 || $totaalBedrag < -0.01) {
                        $heeftBetaalbaarBedrag = true;
                        $output->writeln('.... We need to send invoice');
                    }
                }
                if ($heeftBetaalbaarBedrag === false) {
                    $output->writeln('.. Skip (no invoiced needed)');
                    continue;
                }

                $pdf = $this->pdfFactuurService->generate($koopman, $dagvergunningen);
                $pdfFile = $pdf->Output('koopman-' . $koopman->getId() . '.pdf', 'S');

                $body =  "Bijgesloten ontvangt u een BTW- overzicht van het Marktbureau van de Gemeente Amsterdam als PDF-bestand. \n";
                $body .= "Dit is voor uw eigen administratie. \n"; 
                $body .= "U heeft reeds betaald of u moet nog betalen op de markt, per pin, bij de markttoezichthouder. \n";
                $body .= "Het gaat hier niet om een factuur.";

                $message = (new \Swift_Message())
                    ->setSubject('BTW- overzicht Marktbureau Gemeente Amsterdam')
                    ->setFrom(['marktbureau@amsterdam.nl' => 'Marktbureau Gemeente Amsterdam'])
                    ->setTo([$koopman->getEmail()])
                    ->setBody($body)
                    ->attach((new \Swift_Attachment())->setFilename('btw-overzicht.pdf')->setContentType('application/pdf')->setBody($pdfFile))
                ;

                $this->mailer->send($message);
                $output->writeln('.. Mail queued for ' . $koopman->getEmail());
            } catch (\Exception $e) {
                $output->writeln('.. Failure ' . get_class($e) . ' ::: ' . $e->getMessage());
            }
        }

        return 0;
    }
}
