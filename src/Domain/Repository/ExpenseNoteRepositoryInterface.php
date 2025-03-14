<?php

namespace App\Domain\Repository;

use App\Domain\Entity\ExpenseNote;
use Ramsey\Uuid\UuidInterface;

interface ExpenseNoteRepositoryInterface
{
    public function save(ExpenseNote $expenseNote): void;

    public function findById(UuidInterface $id): ?ExpenseNote;

    public function findAll(): array;

    public function delete(ExpenseNote $expenseNote): void;
}
