<?php

namespace App\UI\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateExpenseRequest
{
    #[Assert\NotBlank(message: "User ID is required.")]
    #[Assert\Uuid(message: "Invalid User ID format.")]
    public string $userId;

    #[Assert\NotBlank(message: "Company ID is required.")]
    #[Assert\Uuid(message: "Invalid Company ID format.")]
    public string $companyId;

    #[Assert\NotBlank(message: "Date is required.")]
    #[Assert\Date(message: "Invalid date format (expected Y-m-d).")]
    public string $date;

    #[Assert\NotBlank(message: "Amount is required.")]
    #[Assert\Type("float", message: "Amount must be a valid number.")]
    #[Assert\Positive(message: "Amount must be greater than zero.")]
    public float $amount;

    #[Assert\NotBlank(message: "Type is required.")]
    #[Assert\Choice(["meal", "transport", "hotel", "other"], message: "Invalid expense type.")]
    public string $type;

    public function __construct(array $data)
    {
        $this->userId = $data['userId'] ?? null;
        $this->companyId = $data['companyId'] ?? null;
        $this->date = $data['date'] ?? null;
        $this->amount = isset($data['amount']) ? (float) $data['amount'] : null;
        $this->type = $data['type'] ?? null;
    }
}
