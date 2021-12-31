<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Token;
use App\Entity\Koopman;
use App\Entity\Vervanger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;
use App\Controller\KoopmanController;

class VervangerTestCase extends WebTestCase
{
    /** @var Client */
    protected $client;

    /** @var EntityManagerInterface $entityManager */
    protected $em;

    protected function setUp(): void{
        $this->client = static::createClient(
            array(), array('HTTP_HOST' => 'http://172.101.0.1:8081') 
            );
        $this->em = $this->client->getContainer()->get("doctrine.orm.default_entity_manager");
    }

    public function testVervangerIdinKoopmanObject():void{
        
        $tokenRepository = $this->em->getRepository(Token::class);
        $vervangerRepository = $this->em->getRepository(Vervanger::class);

        $token = $tokenRepository->findOneBy(['clientApp' => 'admin']);
        $vervanger = $vervangerRepository->findOneBy(['id' => 1]);
        
        $expected_vervanger_id = $vervanger->getVervangerId();
        $koopman_id = $vervanger->getKoopman()->getId();

        $headers = [
            'Content-Type' => 'application/json',
            'HTTP_Authorization' => 'Bearer ' . $token->getUuid(),
            'HTTP_MmAppKey' => 'insecure',
        ];
        var_dump($this->client);

        $this->client->request('GET', '/api/1.1.0/koopman/id/'.$koopman_id, array(), array(), $headers);
        $this->assertResponseIsSuccessful();
        $resp = $this->client->getResponse();
        $response_data = json_decode($resp->getContent(), true);
        $vervanger_id = $response_data['vervangers'][0]['vervanger_id'];

	    $this->assertTrue($expected_vervanger_id === $vervanger_id);
    }

}
