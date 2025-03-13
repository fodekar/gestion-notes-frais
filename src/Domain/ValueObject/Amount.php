<?php

namespace App\Domain\ValueObject;

use InvalidArgumentException;

class Amount
{
    private float $value;

    public function __construct(float $amount)
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Amount must be greater than 0.");
        }
        $this->value = $amount;
    }

    public function getValue(): float
    {
        return $this->value;
    }
}
