<?php

namespace App\Tests;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Test\ApiTestCase;

class RsvpPatternControllerTest extends ApiTestCase
{
    private $attending;
    private $marktId;
    private $erkenningsNummer;
    private $monday;
    private $tuesday;
    private $wednesday;
    private $thursday;
    private $friday;
    private $saturday;
    private $sunday;

    protected function setUp(): void
    {
        parent::setUp();

        $marktRepository = $this->entityManager->getRepository(Markt::class);
        $markt = $marktRepository->findOneBy([]);

        $koopmanRepository = $this->entityManager->getRepository(Koopman::class);
        $koopman = $koopmanRepository->findOneBy([]);

        $this->monday = true;
        $this->tuesday = true;
        $this->wednesday = true;
        $this->thursday = true;
        $this->friday = true;
        $this->saturday = true;
        $this->sunday = true;
        $this->marktId = $markt->getId();
        $this->erkenningsNummer = $koopman->getErkenningsnummer();
    }

    private function rsvpPatternGet($url)
    {
        $response = $this->client->get($url, ['headers' => $this->headers]);
        $responseData = json_decode((string) $response->getBody(), true);
        foreach ($responseData as $rsvpPattern) {
            $this->assertArrayHasKey('markt', $rsvpPattern);
            $this->assertArrayHasKey('koopman', $rsvpPattern);
            $this->assertArrayHasKey('monday', $rsvpPattern);
            $this->assertArrayHasKey('tuesday', $rsvpPattern);
            $this->assertArrayHasKey('wednesday', $rsvpPattern);
            $this->assertArrayHasKey('thursday', $rsvpPattern);
            $this->assertArrayHasKey('friday', $rsvpPattern);
            $this->assertArrayHasKey('saturday', $rsvpPattern);
            $this->assertArrayHasKey('sunday', $rsvpPattern);
        }
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateRsvpPattern()
    {
        $data = [
            'marktId' => $this->marktId,
            'erkenningsNummer' => $this->erkenningsNummer,
            'monday' => $this->monday,
            'tuesday' => $this->tuesday,
            'wednesday' => $this->wednesday,
            'thursday' => $this->thursday,
            'friday' => $this->friday,
            'saturday' => $this->saturday,
            'sunday' => $this->sunday,
        ];

        $response = $this->client->post('/api/1.1.0/rsvp_pattern', ['headers' => $this->headers, 'body' => json_encode($data)]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    // public function testGetRsvpByMarktAndErkenningsnummer()
    // {
    //     $this->rsvpPatternGet("/api/1.1.0/rsvp_pattern/markt/$this->marktId/koopman/$this->erkenningsNummer");
    // }

    public function testGetRsvpByErkenningsnummer()
    {
        $this->rsvpPatternGet("/api/1.1.0/rsvp_pattern/koopman/$this->erkenningsNummer");
    }
}
