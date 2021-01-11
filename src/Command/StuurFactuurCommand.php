<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\KoopmanRepository;
use App\Repository\DagvergunningRepository;
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

    public function __construct(KoopmanRepository $koopmanRepository, DagvergunningRepository $dagvergunningRepository, PdfFactuurService $pdfFactuurService, \Swift_Mailer $mailer)
    {
        $this->koopmanRepository = $koopmanRepository;
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->pdfFactuurService = $pdfFactuurService;
        $this->mailer = $mailer;

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
                $pdf = $this->pdfFactuurService->generate($koopman, $dagvergunningen);
                $pdfFile = $pdf->Output('koopman-' . $koopman->getId() . '.pdf', 'S');

                $message = (new \Swift_Message())
                    ->setSubject('Factuur Marktbureau Gemeente Amsterdam')
                    ->setFrom(['marktbureau@amsterdam.nl' => 'Marktbureau Gemeente Amsterdam'])
                    ->setTo([$koopman->getEmail()])
                    ->setBody('Bijgesloten ontvangt u een factuur van het Marktbureau van de Gemeente Amsterdam als PDF-bestand. Deze factuur is voor uw eigen administratie en bevat tevens een btw specificatie.

De factuur heeft u reeds betaald of moet u nog betalen op de markt per pin bij de toezichthouder. De factuur is geen betalingsbewijs.')
                    ->attach((new \Swift_Attachment())->setFilename('factuur.pdf')->setContentType('application/pdf')->setBody($pdfFile))
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