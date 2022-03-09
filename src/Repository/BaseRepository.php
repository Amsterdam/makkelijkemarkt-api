<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class BaseRepository extends ServiceEntityRepository
{
    public function findAllAsMap(string $columnName): array
    {
        $mappedItems = [];

        $allItems = $this->findAll();

        $getterFunctionName = 'get'.ucfirst($columnName);

        foreach ($allItems as $item) {
            $mappedItems[$item->$getterFunctionName()] = $item;
        }

        return $mappedItems;
    }
}
