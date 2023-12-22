<?php

namespace App\Tests;

use App\Entity\Koopman;
use App\Entity\Markt;
use App\Test\ApiTestCase;

class RsvpControllerTest extends ApiTestCase
{
    protected $marktDate;
    protected $attending;
    protected $marktId;
    protected $koopmanErkenningsNummer;

    protected function setUp(): void
    {
        parent::setUp();

        $marktRepository = $this->entityManager->getRepository(Markt::class);
        $markt = $marktRepository->findOneBy([]);

        $koopmanRepository = $this->entityManager->getRepository(Koopman::class);
        $koopman = $koopmanRepository->findOneBy([]);

        $this->marktDate = (new \DateTime('now'))->format('Y-m-d');
        $this->attending = true;
        $this->marktId = $markt->getId();
        $this->koopmanErkenningsNummer = $koopman->getErkenningsnummer();
    }

    private function rsvpGet($url)
    {
        $response = $this->client->get($url, ['headers' => $this->headers]);
        $responseData = json_decode((string) $response->getBody(), true);
        foreach ($responseData as $rsvp) {
            $this->assertArrayHasKey('marktDate', $rsvp);
            $this->assertArrayHasKey('attending', $rsvp);
            $this->assertArrayHasKey('markt', $rsvp);
            $this->assertArrayHasKey('koopman', $rsvp);
        }
        $this->assertIsArray($responseData);
        $this->assertNotEmpty($responseData);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPostRsvp()
    {
        $data = [
            'marktDate' => $this->marktDate,
            'attending' => $this->attending,
            'marktId' => $this->marktId,
            'koopmanErkenningsNummer' => $this->koopmanErkenningsNummer,
        ];

        $response = $this->client->post('/api/1.1.0/rsvp', ['headers' => $this->headers, 'body' => json_encode($data)]);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetRsvpByErkenningsnummer()
    {
        $this->rsvpGet('/api/1.1.0/rsvp/koopman/'.$this->koopmanErkenningsNummer);
    }

    public function testGetRsvpByMarktIdAndMarktDate()
    {
        $this->rsvpGet('/api/1.1.0/rsvp/markt/'.$this->marktId.'/date/'.$this->marktDate);
    }

    public function testGetRsvpByMarktIdAndErkenningsNummer()
    {
        $this->rsvpGet('/api/1.1.0/rsvp/markt/'.$this->marktId.'/koopman/'.$this->koopmanErkenningsNummer);
    }
}
