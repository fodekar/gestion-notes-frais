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

    /**
     * 🔹 Créer une note de frais
     */
    public function createExpenseNote(
        User              $user,
        UuidInterface     $companyId,
        DateTimeImmutable $date,
        float             $amount,
        string            $type
    ): ExpenseNote
    {
        // Vérifier que la société existe
        $company = $this->companyRepository->findById($companyId);
        if (!$company) {
            throw new InvalidArgumentException("Company not found.");
        }

        // Valider le type
        $expenseType = new ExpenseType($type);

        // Créer la note de frais
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

    /**
     * 🔹 Mettre à jour une note de frais (seulement si elle appartient à l'utilisateur)
     */
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

        // Valider le type
        $expenseType = new ExpenseType($type);

        // Mettre à jour les valeurs
        $expenseNote->update(
            $date,
            new Amount($amount),
            $expenseType
        );

        $this->expenseNoteRepository->save($expenseNote);

        return $expenseNote;
    }

    /**
     * 🔹 Récupérer toutes les notes de frais d'un utilisateur
     */
    public function getExpensesForUser(User $user): array
    {
        return $this->expenseNoteRepository->findByUser($user);
    }

    /**
     * 🔹 Vérifier qu'une note de frais appartient bien à l'utilisateur
     */
    public function getExpenseNoteByIdAndUser(UuidInterface $id, User $user): ?ExpenseNote
    {
        $expense = $this->expenseNoteRepository->findById($id);
        return ($expense && $expense->getUser()->getId()->toString() === $user->getId()->toString()) ? $expense : null;
    }

    /**
     * 🔹 Supprimer une note de frais (seulement si elle appartient à l'utilisateur)
     */
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
