<?php

declare(strict_types=1);

namespace App\Test;

use App\Entity\Account;
use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ApiTestCase extends WebTestCase
{
    /** @var Client */
    private static $staticClient;

    /** @var Generator */
    private static $staticFaker;

    /** @var Client */
    protected $client;

    /** @var Generator */
    protected $faker;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var array<string> */
    protected $headers;

    protected static KernelBrowser $browser;

    public static function setUpBeforeClass(): void
    {
        self::$browser = static::createClient();

        self::$staticClient = new Client([
            'base_uri' => 'http://mm-api_nginx:80',
        ]);

        self::$staticFaker = Factory::create();
    }

    protected function setUp(): void
    {
        $this->client = self::$staticClient;
        $this->faker = self::$staticFaker;

        $this->purgeDatabase();

        $this->entityManager = $this->getService('doctrine.orm.default_entity_manager');

        $accountRepository = $this->entityManager->getRepository(Account::class);
        $tokenRepository = $this->entityManager->getRepository(Token::class);

        $account = $accountRepository->findOneBy([
            'naam' => 'Salmagundi',
        ]);

        /** @var Token $token */
        $token = $tokenRepository->findOneBy([
            'account' => $account->getId(),
        ]);

        $this->headers = [
            'HTTP_Content-Type' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.$token->getUuid(),
            'HTTP_MmAppKey' => $_SERVER['MM_APP_KEY'],
        ];
    }

    private function request(string $method, string $url, array $options): array
    {
        $query = $options[RequestOptions::QUERY] ?? [];

        $headers = $options[RequestOptions::HEADERS] ?? [];
        $headers = array_merge($headers, $this->headers);

        $body = $options[RequestOptions::JSON] ?? null;

        self::$browser->request($method, $url, $query, [], $headers, $body);

        return json_decode(self::$browser->getResponse()->getContent(), true);
    }

    protected function getResponseHeader(string $name): ?string
    {
        return self::$browser->getResponse()->headers->get($name);
    }

    protected function get(string $url, array $options): array
    {
        return $this->request('GET', $url, $options);
    }

    protected function post(string $url, array $options): array
    {
        return $this->request('POST', $url, $options);
    }

    protected function put(string $url, array $options): array
    {
        return $this->request('PUT', $url, $options);
    }

    protected function delete(string $url, array $options): array
    {
        return $this->request('DELETE', $url, $options);
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
