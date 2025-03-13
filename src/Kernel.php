<?php

namespace App;

use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Doctrine\UuidType;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();

        if (!Type::hasType('uuid')) {
            Type::addType('uuid', UuidType::class);
            $connection = $this->getContainer()->get('doctrine')->getConnection();
            $connection->getDatabasePlatform()->registerDoctrineTypeMapping('uuid', 'string');
        }
    }

}
