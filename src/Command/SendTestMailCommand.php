<?php

declare(strict_types=1);

namespace App\Command;

use App\Utils\Logger;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendTestMailCommand extends Command
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;

        parent::__construct();
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('makkelijkemarkt:testmail');
        $this->setDescription('Verstuur een mail om de mail service te testen');
        $this->addArgument('email', InputArgument::REQUIRED, 'Email to sent to');
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = new Logger();
        $logger->addOutput($output);

        $email = $input->getArgument('email');
        $output->writeln("Got email address: $email");

        try {
            $message = (new Swift_Message())
                ->setSubject('Test email Makkelijke Markt API')
                ->setFrom(['Salmagundi-Markten@amsterdam.nl' => 'Team Salmagundi'])
                ->setTo([$email])
                ->setBody('Dit is een test e-mail om te valideren of e-mails goed worden verzonden.')
            ;

            $this->mailer->send($message);
            $output->writeln('Mail sent...');
        } catch (\Exception $e) {
            $output->writeln('.. Failure '.get_class($e).' ::: '.$e->getMessage());

            return COMMAND::FAILURE;
        }

        return COMMAND::SUCCESS;
    }
}
