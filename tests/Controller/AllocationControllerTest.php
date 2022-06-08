<?php

namespace App\Tests;

use App\Entity\Allocation;
use App\Entity\Koopman;
use App\Entity\Markt;
use App\Test\ApiTestCase;

class AllocationControllerTest extends ApiTestCase
{
    private $indeling;
    private Koopman $allocationKoopman;
    private $koopmannen;

    protected function setUp(): void
    {
        parent::setUp();
        $em = $this->entityManager;
        $rep = $em->getRepository(Koopman::class);

        $marktRepository = $em->getRepository(Markt::class);

        $this->dapperMarkt = $marktRepository->getByAfkorting('DAPP');

        $indeling = ['afwijzingen' => [], 'toewijzingen' => []];

        // Find the first 3 Koopmannen
        [$kp_1, $kp_2, $kp_3] = $rep->findBy([], [], 3);
        $entities = [$kp_1, $kp_2, $kp_3];
        $this->koopmannen = $entities;
        foreach ($entities as $id => $koopman) {
            $alloc = [];
            $alloc['marktId'] = $this->dapperMarkt->getId();
            $alloc['marktDate'] = '2021-12-31';
            $alloc['erkenningsNummer'] = $koopman->getErkenningsNummer();
            $alloc['ondernemer'] = [
                'description' => $koopman->getAchternaam(),
                'erkenningsNummer' => $koopman->getErkenningsnummer(),
                'status' => 'soll',
                'sollicitatieNummer' => $koopman->getPerfectViewNummer(),
                'plaatsen' => [],
                'voorkeur' => ['brancheId' => '101-agf',
                    'anywhere' => true,
                    'maximum' => 2, 'minimum' => 1, 'parentBrancheId' => true, 'verkoopinrichting' => [], ],
            ];
            if (0 === $id) {
                $this->allocationKoopman = $koopman;
                $alloc['plaatsen'] = ['1', '2'];
                $indeling['toewijzingen'][] = $alloc;
            } else {
                $alloc['reason'] = ['code' => 2, 'message' => 'Geen geschikte locatie'];
                $indeling['afwijzingen'][] = $alloc;
            }
        }
        $this->indeling = json_encode($indeling);
    }

    public function testGetAll(): void
    {
        $response = $this->client->get(
            '/api/1.1.0/allocation/markt/' . $this->dapperMarkt->getId() . '/date/2021-12-31',
            ['headers' => $this->headers]
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostAllocations(): void
    {
        $this->client->post('/api/1.1.0/allocation/markt/' . $this->dapperMarkt->getId() . '/date/2021-12-31', [
            'headers' => $this->headers,
            'body' => $this->indeling,
        ]);

        $response = $this->client->get('/api/1.1.0/allocation/markt/' . $this->dapperMarkt->getId() . '/date/2021-12-31', ['headers' => $this->headers]);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertEquals(3, count($responseData));

        $allocation_found = false;
        $rejection_found = false;
        foreach ($responseData as $result) {
            if ($result['koopman'] == $this->allocationKoopman->getErkenningsnummer()) {
                // assert toewijzing
                $this->assertEquals('1', $result['plaatsen'][0]);
                $this->assertEquals('2', $result['plaatsen'][1]);
                $allocation_found = true;
            } else {
                $rejection_found = true;
                $this->assertFalse($result['isAllocated']);
            }
        }
        $this->assertTrue($allocation_found);
        $this->assertTrue($rejection_found);
    }

    protected function tearDown(): void
    {
        $em = $this->entityManager;
        $rep = $em->getRepository(Allocation::class);
        $entities = $rep->findAll();
        foreach ($entities as $entity) {
            $em->remove($entity);
        }
        $em->flush();
    }
}
