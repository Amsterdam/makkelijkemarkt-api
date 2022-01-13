<?php

declare(strict_types=1);

namespace App\Utils;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Logger implements LoggerInterface
{
    /**
     * @var array
     */
    protected $outputStreams = [];

    /**
     * @var array
     */
    protected $store = [];

    /**
     * @param mixed $output "stdout"|"STDOUT"|"store"|LoggerInterface|OutputInterface
     */
    public function addOutput($output)
    {
        $this->outputStreams[] = $output;
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::emergency()
     */
    public function emergency($message, array $context = [])
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::alert()
     */
    public function alert($message, array $context = [])
    {
        $this->log('alert', $message, $context);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::critical()
     */
    public function critical($message, array $context = [])
    {
        $this->log('critical', $message, $context);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::error()
     */
    public function error($message, array $context = [])
    {
        $this->log('error', $message, $context);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::warning()
     */
    public function warning($message, array $context = [])
    {
        $this->log('warning', $message, $context);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::notice()
     */
    public function notice($message, array $context = [])
    {
        $this->log('notice', $message, $context);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::info()
     */
    public function info($message, array $context = [])
    {
        $this->log('info', $message, $context);
    }

    /**
     * (non-PHPdoc).
     *
     * @see \Psr\Log\LoggerInterface::debug()
     */
    public function debug($message, array $context = [])
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @param string $level
     * @param string $message
     */
    public function log($level, $message, array $context = [])
    {
        foreach ($this->outputStreams as $outputStream) {
            switch (true) {
                case 'stdout' === $outputStream:
                case 'STDOUT' === $outputStream:
                    echo '['.$level.'] '.$message.' : '.json_encode($context).PHP_EOL;
                    break;
                case 'store' === $outputStream:
                    $this->store[] = ['level' => $level, 'message' => $message, 'context' => json_encode($context)];
                    break;
                case $outputStream instanceof LoggerInterface:
                    $outputStream->log($level, $message, $context);
                    break;
                case $outputStream instanceof OutputInterface:
                    switch ($level) {
                        case 'emergency':
                        case 'alert':
                        case 'critical':
                        case 'error':
                            $outputStream->writeln('<error>'.$level.'</error>');
                            // no break
                        case 'warning':
                        case 'notice':
                            $outputStream->writeln('<notice>'.$level.'</notice>');
                            // no break
                        case 'info':
                        default:
                            $outputStream->writeln('<info>'.$level.'</info>');
                    }
                    $outputStream->writeln($message);
                    if ([] !== $context) {
                        $outputStream->writeln(json_encode($context));
                    }
                    break;
            }
        }
    }
}
