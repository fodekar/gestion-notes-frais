<?php

namespace App\UI\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateExpenseRequest
{
    #[Assert\NotBlank(message: "Date is required.")]
    #[Assert\Date(message: "Invalid date format (expected Y-m-d).")]
    public string $date;

    #[Assert\NotBlank(message: "Amount is required.")]
    #[Assert\Type("float", message: "Amount must be a valid number.")]
    #[Assert\Positive(message: "Amount must be greater than zero.")]
    public float $amount;

    #[Assert\NotBlank(message: "Type is required.")]
    #[Assert\Choice(["meal", "fuel", "toll", "conference"], message: "Invalid expense type.")]
    public string $type;

    public function __construct(array $data)
    {
        $this->date = $data['date'] ?? null;
        $this->amount = isset($data['amount']) ? (float) $data['amount'] : null;
        $this->type = $data['type'] ?? null;
    }
}
