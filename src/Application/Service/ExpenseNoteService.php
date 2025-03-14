<?php

namespace App\Application\Service;

use App\Domain\Entity\ExpenseNote;
use App\Domain\Entity\User;
use App\Domain\Repository\ExpenseNoteRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\CompanyRepositoryInterface;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\ExpenseType;
use DateTimeImmutable;
use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;

class ExpenseNoteService
{
    private ExpenseNoteRepositoryInterface $expenseNoteRepository;
    private UserRepositoryInterface $userRepository;
    private CompanyRepositoryInterface $companyRepository;

    public function __construct(
        ExpenseNoteRepositoryInterface $expenseNoteRepository,
        UserRepositoryInterface        $userRepository,
        CompanyRepositoryInterface     $companyRepository
    )
    {
        $this->expenseNoteRepository = $expenseNoteRepository;
        $this->userRepository = $userRepository;
        $this->companyRepository = $companyRepository;
    }

    public function createExpenseNote(
        User              $user,
        UuidInterface     $companyId,
        DateTimeImmutable $date,
        float             $amount,
        string            $type
    ): ExpenseNote
    {
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new InvalidArgumentException("Company not found.");
        }

        $expenseType = new ExpenseType($type);

        $expenseNote = new ExpenseNote(
            $date,
            new Amount($amount),
            $expenseType,
            $user,
            $company
        );

        $this->expenseNoteRepository->save($expenseNote);

        return $expenseNote;
    }

    public function updateExpenseNote(
        UuidInterface     $id,
        DateTimeImmutable $date,
        float             $amount,
        string            $type,
        User              $user
    ): ?ExpenseNote
    {
        $expenseNote = $this->getExpenseNoteByIdAndUser($id, $user);

        if (!$expenseNote) {
            return null;
        }

        $expenseType = new ExpenseType($type);

        $expenseNote->update(
            $date,
            new Amount($amount),
            $expenseType
        );

        $this->expenseNoteRepository->save($expenseNote);

        return $expenseNote;
    }


    public function getExpensesForUser(User $user): array
    {
        return $this->expenseNoteRepository->findByUser($user);
    }


    public function getExpenseNoteByIdAndUser(UuidInterface $id, User $user): ?ExpenseNote
    {
        $expense = $this->expenseNoteRepository->findById($id);
        return ($expense && $expense->getUser()->getId()->toString() === $user->getId()->toString()) ? $expense : null;
    }

    public function deleteExpenseNote(UuidInterface $id, User $user): bool
    {
        $expense = $this->getExpenseNoteByIdAndUser($id, $user);
        if (!$expense) {
            return false;
        }

        $this->expenseNoteRepository->delete($expense);
        return true;
    }
}
