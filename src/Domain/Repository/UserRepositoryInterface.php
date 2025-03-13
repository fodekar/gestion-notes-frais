<?php

namespace App\Domain\Repository;

use App\Domain\Entity\User;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    public function findById(UuidInterface $id): ?User;
}
