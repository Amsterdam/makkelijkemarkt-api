<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AbstractBasicEntity;
use App\Normalizer\EntityNormalizer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use http\Exception\InvalidArgumentException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractBasicController extends AbstractController
{
    protected const BASIC_DATABASE_FIELDS = ['naam'];

    protected LoggerInterface $logger;
    protected EntityManagerInterface $entityManager;
    protected ServiceEntityRepositoryInterface $repository;
    protected Serializer $serializer;

    private string $entityClassName;

    public function __construct(
        CacheManager $cacheManager,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ManagerRegistry $managerRegistry
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->serializer = new Serializer([new EntityNormalizer($cacheManager)], [new JsonEncoder()]);

        $this->entityClassName = $this->getEntityClassname();

        if (!new $this->entityClassName() instanceof AbstractBasicEntity) {
            throw new InvalidArgumentException('Entity should be instance of '.AbstractBasicEntity::class);
        }

        $this->repository = new ServiceEntityRepository($managerRegistry, $this->entityClassName);
    }

    abstract protected function getEntityClassname(): string;

    private function getNewEntity(): AbstractBasicEntity
    {
        return new $this->entityClassName();
    }

    public function create(Request $request): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        foreach (self::BASIC_DATABASE_FIELDS as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $instance = $this->getNewEntity();
        $instance->setNaam($data['naam']);

        $this->entityManager->persist($instance);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($instance, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    public function getAll(): Response
    {
        $instances = $this->repository->findAll();

        $response = $this->serializer->serialize($instances, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    public function getById(string $id): Response
    {
        $instance = $this->repository->find($id);

        if (null === $instance) {
            return new JsonResponse(['error' => $this->entityClassName.' not found.'], Response::HTTP_NOT_FOUND);
        }

        $response = $this->serializer->serialize($instance, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    public function update(Request $request, string $id): Response
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => json_last_error_msg()], Response::HTTP_BAD_REQUEST);
        }

        $instance = $this->repository->find($id);

        if (null === $instance) {
            return new JsonResponse(['error' => $this->entityClassName.$id." doesn't exist"], Response::HTTP_NOT_FOUND);
        }

        foreach (self::BASIC_DATABASE_FIELDS as $expectedParameter) {
            if (!array_key_exists($expectedParameter, $data)) {
                return new JsonResponse(['error' => "parameter '".$expectedParameter."' missing"], Response::HTTP_BAD_REQUEST);
            }
        }

        $instance->setNaam($data['naam']);

        $this->entityManager->persist($instance);
        $this->entityManager->flush();

        $response = $this->serializer->serialize($instance, 'json');

        return new Response($response, Response::HTTP_OK, ['Content-type' => 'application/json']);
    }

    public function delete(string $id): JsonResponse
    {
        $instance = $this->repository->find($id);

        if (null === $instance) {
            return new JsonResponse(['error' => $this->entityClassName.' not found.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($instance);
        $this->entityManager->flush();

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
