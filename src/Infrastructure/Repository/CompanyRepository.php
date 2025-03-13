<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Company;
use App\Domain\Repository\CompanyRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;

class CompanyRepository implements CompanyRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Company::class);
    }

    public function findById(UuidInterface $id): ?Company
    {
        return $this->repository->find($id);
    }
}
