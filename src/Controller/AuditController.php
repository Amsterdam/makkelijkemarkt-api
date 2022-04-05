<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Dagvergunning;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Normalizer\EntityNormalizer;
use App\Repository\DagvergunningRepository;
use App\Repository\MarktRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * @OA\Tag(name="Audit")
 */
final class AuditController extends AbstractController
{
    /** @var DagvergunningRepository */
    private $dagvergunningRepository;

    /** @var MarktRepository */
    private $marktRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CacheManager */
    public $cacheManager;

    /** @var Serializer */
    private $serializer;

    /** @var array<string> */
    private $groups;

    public function __construct(
        DagvergunningRepository $dagvergunningRepository,
        MarktRepository $marktRepository,
        EntityManagerInterface $entityManager,
        CacheManager $cacheManager
    ) {
        $this->dagvergunningRepository = $dagvergunningRepository;
        $this->marktRepository = $marktRepository;
        $this->entityManager = $entityManager;

        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);
        $this->groups = [
            'dagvergunning',
            'simpleKoopman',
            'vervanger',
            'simpleMarkt',
            'account',
            'vergunningControle',
            'factuur',
            'simpleProduct',
        ];
    }

    /**
     * @OA\Get(
     *     path="/api/1.1.0/audit/{marktId}/{datum}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AuditGetAllByMarktIdAndDatum",
     *     tags={"Audit"},
     *     summary="Haal de lijst van te auditen dagvergunning op",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="datum", @OA\Schema(type="string"), in="path", required=true, description="datum YYYY-MM-DD"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Dagvergunning"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/audit/{marktId}/{datum}", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function getAllByMarktIdAndDatum(int $marktId, string $datum): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $date = new DateTime($datum);

        $dagvergunningen = $this->dagvergunningRepository->findBy([
            'audit' => true,
            'markt' => $markt,
            'dag' => $date,
            'doorgehaald' => false,
        ]);

        $response = $this->serializer->serialize($dagvergunningen, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($dagvergunningen),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/audit/{marktId}/{datum}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AuditPost",
     *     tags={"Audit"},
     *     summary="Maak of werk audit dagvergunning bij voor een markt en dag kombinatie",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="datum", @OA\Schema(type="string"), in="path", required=true, description="datum YYYY-MM-DD"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Dagvergunning"))
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/audit/{marktId}/{datum}", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function post(int $marktId, string $datum): Response
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $now = new DateTime($datum);
        $now->setTime(0, 0, 0);

        $dagvergunningen = $this->dagvergunningRepository->findBy([
            'audit' => true,
            'markt' => $markt,
            'dag' => $now,
            'doorgehaald' => false,
        ]);

        // if there is already an audit list, return it
        if (count($dagvergunningen)) {
            $response = $this->serializer->serialize($dagvergunningen, 'json', ['groups' => $this->groups]);

            return new Response($response, Response::HTTP_OK, [
                'Content-type' => 'application/json',
                'X-Api-ListSize' => count($dagvergunningen),
            ]);
        }

        $this->entityManager->beginTransaction();

        // alle dagvergunningen van vandaag voor deze markt
        $dagvergunningenToday = $this->dagvergunningRepository->findAllByMarktAndDag($markt, $now, false);

        // deze lijst zal straks alle dagvergunningen bevatten die gecontroleerd moeten worden
        $audits = [];

        // iedereen met een handhavingsverzoek toevoegen aan de lijst

        /** @var Dagvergunning $dagvergunning */
        foreach ($dagvergunningenToday as $dagvergunning) {
            /** @var Koopman $koopman */
            $koopman = $dagvergunning->getKoopman();

            if (null !== $koopman->getHandhavingsVerzoek() &&
                $now <= $koopman->getHandhavingsVerzoek()
            ) {
                $dagvergunning->setAuditReason(Dagvergunning::AUDIT_HANDHAVINGS_VERZOEK);
                $dagvergunning->setAudit(true);
                $audits[] = $dagvergunning;
            }
        }

        // aantallen die worden getrokken uit elke poule
        $aantalPouleA = floor((($markt->getAuditMax() - count($audits)) / 100) * 25); // iedereen - handhavingsverzoek
        $aantalPouleB = ceil((($markt->getAuditMax() - count($audits)) / 100) * 75); // 's ochtends niet zelf aanwezig - handhavingsverzoek

        // maak twee poules waarin iedereen wordt toegevoegd

        $pouleA = (new ArrayCollection($dagvergunningenToday))->filter(function (Dagvergunning $dagvergunning) use ($now) {
            /** @var Koopman $koopman */
            $koopman = $dagvergunning->getKoopman();

            // verwijder iedereen uit deze poule die al in de lijst zit (want handhavingsverzoek)
            if (null !== $koopman->getHandhavingsVerzoek() &&
                $now <= $koopman->getHandhavingsVerzoek()
            ) {
                return false;
            }

            // verwijder iedereen uit deze poule die NIET zelf aanwezig was
            if ('zelf' !== $dagvergunning->getAanwezig()) {
                return false;
            }

            return true;
        });

        $pouleB = (new ArrayCollection($dagvergunningenToday))->filter(function (Dagvergunning $dagvergunning) use ($now) {
            /** @var Koopman $koopman */
            $koopman = $dagvergunning->getKoopman();

            // verwijder iedereen uit deze poule die al in de lijst zit (want handhavingsverzoek)
            if (null !== $koopman->getHandhavingsVerzoek() &&
                $now <= $koopman->getHandhavingsVerzoek()
            ) {
                return false;
            }

            // verwijder iedereen uit deze poule die zelf aanwezig was
            if ('zelf' === $dagvergunning->getAanwezig()) {
                return false;
            }

            return true;
        });

        $pouleBselected = 0;

        while ($pouleB->count() > 0 && $pouleBselected < $aantalPouleB) {
            $key = array_rand($pouleB->toArray());
            $dagvergunning = $pouleB->get($key);

            $dagvergunning->setAudit(true);
            $dagvergunning->setAuditReason(Dagvergunning::AUDIT_VERVANGER_ZONDER_TOESTEMMING);
            $audits[] = $dagvergunning;
            $pouleB->removeElement($dagvergunning);
            ++$pouleBselected;
        }

        $pouleAselected = 0;

        while ($pouleA->count() > 0 && $pouleAselected < $aantalPouleA) {
            $key = array_rand($pouleA->toArray());
            $dagvergunning = $pouleA->get($key);

            $dagvergunning->setAudit(true);
            $dagvergunning->setAuditReason(Dagvergunning::AUDIT_LOTEN);
            $audits[] = $dagvergunning;
            $pouleA->removeElement($dagvergunning);
            ++$pouleAselected;
        }

        while ($pouleA->count() > 0 && (count($audits) < ($markt->getAuditMax()))) {
            $key = array_rand($pouleA->toArray());
            $dagvergunning = $pouleA->get($key);

            $dagvergunning->setAudit(true);
            $dagvergunning->setAuditReason(Dagvergunning::AUDIT_LOTEN);
            $audits[] = $dagvergunning;
            $pouleA->removeElement($dagvergunning);
        }

        $this->entityManager->flush();
        $this->entityManager->commit();

        $response = $this->serializer->serialize($audits, 'json', ['groups' => $this->groups]);

        return new Response($response, Response::HTTP_OK, [
            'Content-type' => 'application/json',
            'X-Api-ListSize' => count($audits),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/1.1.0/audit_reset/{marktId}/{datum}",
     *     security={{"api_key": {}, "bearer": {}}},
     *     operationId="AuditPostReset",
     *     tags={"Audit"},
     *     summary="Reset te auditen dagvergunning op een markt en dag",
     *     @OA\Parameter(name="marktId", @OA\Schema(type="integer"), in="path", required=true),
     *     @OA\Parameter(name="datum", @OA\Schema(type="string"), in="path", required=true, description="datum YYYY-MM-DD"),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent(@OA\Items(ref="#/components/schemas/Dagvergunning"))
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Not Found",
     *         @OA\JsonContent(@OA\Property(property="error", type="string", description=""))
     *     )
     * )
     * @Route("/audit_reset/{marktId}/{datum}", methods={"POST"})
     * @Security("is_granted('ROLE_SENIOR')")
     */
    public function reset(int $marktId, string $datum): JsonResponse
    {
        /** @var ?Markt $markt */
        $markt = $this->marktRepository->find($marktId);

        if (null === $markt) {
            return new JsonResponse(['error' => 'Markt not found, id = '.$marktId], Response::HTTP_NOT_FOUND);
        }

        $date = new DateTime($datum);

        $dagvergunningen = $this->dagvergunningRepository->findBy([
            'audit' => true,
            'markt' => $markt,
            'dag' => $date,
        ]);

        /** @var Dagvergunning $dagvergunning */
        foreach ($dagvergunningen as $dagvergunning) {
            $dagvergunning->setAudit(false);
            $dagvergunning->setAuditReason(null);

            foreach ($dagvergunning->getVergunningControles() as $controle) {
                $this->entityManager->remove($controle);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_OK, ['X-Api-ListSize' => 0]);
    }
}
