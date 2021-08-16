<?php

declare(strict_types=1);

namespace App\Test;

use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ApiTestCase extends KernelTestCase
{
    /** @var Client */
    private static $staticClient;

    /** @var Generator */
    private static $staticFaker;

    /** @var Client */
    protected $client;

    /** @var Generator */
    protected $faker;

    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;

    /** @var array<string> */
    protected $headers;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();

        self::$staticClient = new Client([
            'base_uri' => 'http://127.0.0.1:8000',
        ]);

        self::$staticFaker = Factory::create();
    }

    protected function setUp(): void
    {
        $this->client = self::$staticClient;
        $this->faker = self::$staticFaker;

        $this->purgeDatabase();

        $this->entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $tokenRepository = $this->entityManager->getRepository(Token::class);

        /** @var Token $token */
        $token = $tokenRepository->findOneBy([
            'account' => 0,
        ]);

        $this->headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token->getUuid(),
            'MmAppKey' => $_SERVER['MM_APP_KEY'],
        ];
    }

    /**
     * Clean up Kernel usage in this test.
     */
    protected function tearDown(): void
    {
        // purposefully not calling parent class, which shuts down the kernel
    }

    private function purgeDatabase(): void
    {
        //$purger = new ORMPurger($this->getService('doctrine')->getManager());
        //$purger->purge();
        // we need the fixtures to have enough data
    }

    /**
     * @return mixed
     */
    protected function getService(string $id)
    {
        return self::$kernel->getContainer()->get($id);
    }

    /**
     * @param array<string, mixed> $data
     *
     * return mixed
     */
    protected function createObject(array $data, object $object): object
    {
        /** @var PropertyAccess $accessor */
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($data as $key => $value) {
            $accessor->setValue($object, $key, $value);
        }

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getService('doctrine.orm.default_entity_manager');
        $entityManager->persist($object);
        $entityManager->flush();

        return $object;
    }
}
