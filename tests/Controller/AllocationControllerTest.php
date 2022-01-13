<?php

namespace App\Tests;

use App\Entity\Allocation;
use App\Entity\Koopman;
use App\Test\ApiTestCase;
use PHPUnit\Framework\TestCase;

class AllocationControllerTest extends ApiTestCase
{
    private $indeling;
    private Koopman $allocationKoopman;


    protected function setUp(): void
    {
        $data = [];
        parent::setUp();
        $em = $this->entityManager;
        $rep = $em->getRepository(Koopman::class);

        $indeling = ["afwijzingen" => [], "toewijzingen" => []];
        $kp_1 = $rep->find(5);
        $kp_2 = $rep->find(6);
        $kp_3 = $rep->find(7);
        $entities = [$kp_1, $kp_2, $kp_3];
        $this->koopmannen = $entities;
        foreach ($entities as $koopman) {
            $alloc = [];
            $alloc["marktId"] = "1";
            $alloc["marktDate"] = "2021-12-31";
            $alloc["erkenningsNummer"] = $koopman->getErkenningsNummer();
            $alloc["ondernemer"] = array(
                "description" => $koopman->getAchternaam(),
                "erkenningsNummer" => $koopman->getErkenningsnummer(),
                "status" => "soll",
                "sollicitatieNummer" => $koopman->getPerfectViewNummer(),
                "plaatsen" => [],
                "voorkeur" => ["brancheId" => "101-agf",
                    "anywhere" => true,
                    "maximum" => 2, "minimum" => 1, 'parentBrancheId' => true, 'verkoopinrichting' => []],
            );
            if ($koopman->getId() == 5) {
                $this->allocationKoopman = $koopman;
                $alloc["plaatsen"] = ["1", "2"];
                $indeling["toewijzingen"][] = $alloc;
            } else {
                $alloc["reason"] = ["code" => 2, "message" => "Geen geschikte locatie"];
                $indeling["afwijzingen"][] = $alloc;
            }
        }
        $json = json_encode($indeling);
        $this->indeling = $json;
    }

    public function testGetAll(): void
    {
        $response = $this->client->get('/api/1.1.0/allocation/DAPP/2021-12-31', ['headers' => $this->headers]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostAllocations(): void
    {
        $this->client->post('/api/1.1.0/allocation/DAPP/2021-12-31', [
            'headers' => $this->headers,
            'body' => $this->indeling,
        ]);

        $response = $this->client->get('/api/1.1.0/allocation/DAPP/2021-12-31', ['headers' => $this->headers]);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode((string)$response->getBody(), true);
        $this->assertEquals(3, count($responseData));

        $allocation_found = false;
        $rejection_found = false;
        foreach ($responseData as $result) {
            if ($result["koopman"] == $this->allocationKoopman->getErkenningsnummer()) {
                // assert toewijzing
                $this->assertEquals("1", $result["plaatsen"][0]);
                $this->assertEquals("2", $result["plaatsen"][1]);
                $allocation_found = true;
            } else {
                $rejection_found = true;
                $this->assertFalse($result["isAllocated"]);
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
