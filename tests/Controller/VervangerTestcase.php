<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Token;
use App\Entity\Koopman;
use App\Entity\Vervanger;
use App\Test\ApiTestCase;

class VervangerTestCase extends ApiTestCase
{

    public function testVervangerIdinKoopmanObject():void{

        $this->em = $this->entityManager; 
        $tokenRepository = $this->em->getRepository(Token::class);
        $vervangerRepository = $this->em->getRepository(Vervanger::class);

        $token = $tokenRepository->findOneBy(['clientApp' => 'admin']);
        $vervanger = $vervangerRepository->findOneBy(['id' => 1]);
        
        $expected_vervanger_id = $vervanger->getVervangerId();
        $koopman_id = $vervanger->getKoopman()->getId();

        $response = $this->client->get('/api/1.1.0/koopman/id/'.$koopman_id, ['headers' => $this->headers]);
        $this->assertEquals(200, $response->getStatusCode());
        $response_data = json_decode((string)$response->getBody(), true);
        $vervanger_id = $response_data['vervangers'][0]['vervanger_id'];

	    $this->assertTrue($expected_vervanger_id === $vervanger_id);
    }

}
