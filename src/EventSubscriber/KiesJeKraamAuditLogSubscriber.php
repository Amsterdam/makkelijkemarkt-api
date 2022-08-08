<?php

namespace App\EventSubscriber;

use App\Entity\KiesJeKraamAuditLog;
use App\Event\KiesJeKraamAuditLogEvent;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KiesJeKraamAuditLogSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KiesJeKraamAuditLogEvent::class => [['onKjkEntityChange', 100]],
        ];
    }

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onKjkEntityChange(KiesJeKraamAuditLogEvent $event)
    {
        $logEntry = (new KiesJeKraamAuditLog())
            ->setActor($event->getActor())
            ->setAction($event->getAction())
            ->setEntityType($event->getEntityType())
            ->setDatetime(new DateTime())
            ->setResult($event->getResult());

        $this->em->persist($logEntry);
        $this->em->flush();
    }
}
