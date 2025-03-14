<?php

namespace App\UI\DTO;

use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;
use App\Domain\Entity\ExpenseNote;

class ExpenseNoteDTO
{
    public string $id;
    public string $date;
    public float $amount;
    public string $type;
    public string $createdAt;
    public string $userId;
    public string $companyId;

    public function __construct(ExpenseNote $expenseNote)
    {
        $this->id = (string)$expenseNote->getId();
        $this->date = $expenseNote->getDate()->format('Y-m-d');
        $this->amount = $expenseNote->getAmount();
        $this->type = $expenseNote->getType();
        $this->createdAt = $expenseNote->getCreatedAt()->format('Y-m-d H:i:s');
        $this->userId = (string)$expenseNote->getUser()->getId();
        $this->companyId = (string)$expenseNote->getCompany()->getId();
    }

    public static function fromEntity(ExpenseNote $expenseNote): self
    {
        return new self($expenseNote);
    }
}
