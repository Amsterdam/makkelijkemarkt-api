<?php

namespace App\Tests\Controller;

use App\DataFixtures\BaseFixture;
use App\Entity\Markt;
use App\Repository\MarktRepository;
use App\Test\ApiTestCase;

class MarktConfiguratieControllerTest extends ApiTestCase
{
    public function testGetLatest(): void
    {
        /** @var MarktRepository $marktRepository */
        $marktRepository = $this->entityManager
            ->getRepository(Markt::class);

        $markt = $marktRepository->findOneBy(['afkorting' => 'AC-2022']);

        $response = $this->client->get(
            "/api/1.1.0/markt/{$markt->getId()}/marktconfiguratie/latest",
            ['headers' => $this->headers]
        );

        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($body['marktId'], $markt->getId());
        $this->assertEquals((bool) strtotime($body['aanmaakDatumtijd']), true);
        $this->assertEquals($body['marktOpstelling']['rows'][0][0], '8');
        $this->assertEquals($body['branches'][0]['brancheId'], '101 - FM - AGF (v)');
        $this->assertEquals($body['geografie']['obstakels'][0]['obstakel'][0], 'loopje');
        $this->assertEquals($body['locaties'][0]['bakType'], 'geen');
        $this->assertEquals($body['paginas'][0]['indelingslijstGroup'][0]['plaatsList'][0], '8');
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
            '/api/1.1.0/markt/-1/marktconfiguratie/latest',
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

        $configuratieData = file_get_contents(
            BaseFixture::FILE_BASED_FIXTURES_DIR.'/marktConfiguratie_simple.json'
        );

        $response = $this->client->post("/api/1.1.0/markt/{$markt->getId()}/marktconfiguratie", [
            'headers' => $this->headers,
            'body' => $configuratieData,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertEquals($response->getStatusCode(), 200);

        $this->assertEquals($body['marktId'], $markt->getId());
        $this->assertEquals((bool) strtotime($body['aanmaakDatumtijd']), true);
        $this->assertEquals($body['marktOpstelling']['rows'][0][0], '1');
        $this->assertEquals($body['branches'][0]['brancheId'], '101 - FM - AGF (v)');
        $this->assertEquals($body['geografie']['obstakels'][0]['obstakel'][0], 'loopje');
        $this->assertEquals($body['locaties'][0]['bakType'], 'geen');
        $this->assertEquals($body['paginas'][0]['indelingslijstGroup'][0]['plaatsList'][0], '1');
    }

    public function testPostMarktDoesNotExist()
    {
        $response = $this->client->post('/api/1.1.0/markt/-1/marktconfiguratie', [
            'headers' => $this->headers,
            'body' => '',
            'http_errors' => false,
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
            'http_errors' => false,
        ]);

        $this->assertEquals($response->getStatusCode(), 400);
    }
}
