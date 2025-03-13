<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

class ExpenseType
{
    public const TYPES = ['fuel', 'toll', 'meal', 'conference'];

    private string $type;

    public function __construct(string $type)
    {
        if (!in_array($type, self::TYPES)) {
            throw new InvalidArgumentException("Invalid expense type.");
        }
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
