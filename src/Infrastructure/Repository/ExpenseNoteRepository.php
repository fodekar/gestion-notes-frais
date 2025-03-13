<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\ExpenseNote;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseNoteRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\UuidInterface;

class ExpenseNoteRepository implements ExpenseNoteRepositoryInterface
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ExpenseNote::class);
    }

    public function save(ExpenseNote $expenseNote): void
    {
        $this->entityManager->persist($expenseNote);
        $this->entityManager->flush();
    }

    public function findById(UuidInterface $id): ?ExpenseNote
    {
        return $this->repository->find($id);
    }

    public function findByUser(User $user): array
    {
        return $this->repository->findBy(['user' => $user]);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function delete(ExpenseNote $expenseNote): void
    {
        $this->entityManager->remove($expenseNote);
        $this->entityManager->flush();
    }
}
