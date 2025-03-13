<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\ExpenseType;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: "expense_notes")]
class ExpenseNote
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?UuidInterface $id = null;

    #[ORM\Column(type: "date_immutable")]
    private DateTimeImmutable $date;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $amount;

    #[ORM\Column(type: "string", length: 50)]
    private string $type;

    #[ORM\Column(type: "datetime_immutable")]
    private DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Company::class)]
    #[ORM\JoinColumn(name: "company_id", referencedColumnName: "id", nullable: false)]
    private Company $company;

    public function __construct(
        DateTimeImmutable $date,
        Amount            $amount,
        ExpenseType       $type,
        User              $user,
        Company           $company
    )
    {
        $this->date = $date;
        $this->amount = $amount->getValue();
        $this->type = $type->getType();
        $this->createdAt = new DateTimeImmutable();
        $this->user = $user;
        $this->company = $company;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = Uuid::fromString($id);
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }

    // Méthodes pour mettre à jour la note de frais
    public function update(DateTimeImmutable $date, Amount $amount, ExpenseType $type): void
    {
        $this->date = $date;
        $this->amount = $amount->getValue();
        $this->type = $type->getType();
    }
}
