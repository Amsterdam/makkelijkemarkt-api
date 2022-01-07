<?php

namespace App\Tests\Controller;

use App\Entity\Markt;
use App\Entity\MarktConfiguratie;
use App\Repository\MarktRepository;
use App\Test\ApiTestCase;
use DateTime;

class MarktConfiguratieControllerTest extends ApiTestCase
{
    public function testGetLatest(): void
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        $markt = $marktRepository->findOneBy([], ['id' => 'ASC']);

        $marktConfiguratie = new MarktConfiguratie();

        $marktConfiguratie->setMarkt($markt)
            ->setAanmaakDatumtijd(new DateTime())
            ->setMarktOpstelling(['testKey' => 1])
            ->setBranches(['testKey' => 2])
            ->setGeografie(['testKey' => 3])
            ->setLocaties(['testKey' => 4])
            ->setPaginas(['testKey' => 5]);

        $this->entityManager->persist($marktConfiguratie);
        $this->entityManager->flush();

        $response = $this->client->get(
            "/api/1.1.0/markt/{$markt->getId()}/marktconfiguratie/latest",
            ['headers' => $this->headers]
        );

        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($body['markt'], $markt->getAfkorting());
        // Should be a date string
        $this->assertEquals((bool)strtotime($body['aanmaakDatumtijd']), true);
        // These are json blobs, so we can't test any validation on the json (could look like anything)
        // So we just test if the json is processed correctly.
        $this->assertEquals($body['marktOpstelling']['testKey'], 1);
        $this->assertEquals($body['branches']['testKey'], 2);
        $this->assertEquals($body['geografie']['testKey'], 3);
        $this->assertEquals($body['locaties']['testKey'], 4);
        $this->assertEquals($body['paginas']['testKey'], 5);
    }

    public function testMarktHasNoMarktConfiguraties()
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        $markt = $marktRepository->findOneBy([], ['id' => 'DESC']);

        $response = $this->client->get(
            "/api/1.1.0/markt/{$markt->getId()}/marktconfiguratie/latest",
            ['headers' => $this->headers, 'http_errors' => false]
        );

        $this->assertEquals($response->getStatusCode(), 404);
    }

    public function testMarktDoesNotExist()
    {
        $response = $this->client->get(
            "/api/1.1.0/markt/-1/marktconfiguratie/latest",
            ['headers' => $this->headers, 'http_errors' => false]
        );

        $this->assertEquals($response->getStatusCode(), 404);
    }

    public function testPost()
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        $markt = $marktRepository->findOneBy([], ['id' => 'ASC']);

        $configuratieData = '{
                            "geografie": {"1": 2},
                            "locaties": {"3": 4},
                            "branches": {"5": 6},
                            "paginas": {"7": 8},
                            "markt": {"9": 10}
                        }';

        $response = $this->client->post("/api/1.1.0/markt/{$markt->getId()}/marktconfiguratie", [
            'headers' => $this->headers,
            'body' => $configuratieData
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals($response->getStatusCode(), 200);

        $this->assertEquals($body['markt'], $markt->getAfkorting());
        // Should be a date string
        $this->assertEquals((bool)strtotime($body['aanmaakDatumtijd']), true);
        // These are json blobs, so we can't test any validation on the json (could look like anything)
        // So we just test if the json is processed correctly.
        $this->assertEquals($body['marktOpstelling']['9'], 10);
        $this->assertEquals($body['branches']['5'], 6);
        $this->assertEquals($body['geografie']['1'], 2);
        $this->assertEquals($body['locaties']['3'], 4);
        $this->assertEquals($body['paginas']['7'], 8);
    }

    public function testPostMarktDoesNotExist()
    {
        $response = $this->client->post("/api/1.1.0/markt/-1/marktconfiguratie", [
            'headers' => $this->headers,
            'body' => '',
            'http_errors' => false
        ]);

        $this->assertEquals($response->getStatusCode(), 404);
    }

    public function testPostInvalidInput()
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        $markt = $marktRepository->findOneBy([], ['id' => 'ASC']);

        // 'markt' key is missing
        $configuratieData = '{
                            "geografie": {"1": 2},
                            "locaties": {"3": 4},
                            "branches": {"5": 6},
                            "paginas": {"7": 8},
                            "nietmarkt": {"9": 10}
                        }';

        $response = $this->client->post("/api/1.1.0/markt/{$markt->getId()}/marktconfiguratie", [
            'headers' => $this->headers,
            'body' => $configuratieData,
            'http_errors' => false
        ]);

        $this->assertEquals($response->getStatusCode(), 400);
    }
}
