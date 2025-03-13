<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Company;
use Ramsey\Uuid\UuidInterface;

interface CompanyRepositoryInterface
{
    public function findById(UuidInterface $id): ?Company;
}
