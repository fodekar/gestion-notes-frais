<?php

namespace App\Infrastructure\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Domain\Entity\User;
use App\Domain\Entity\Company;
use App\Domain\Entity\ExpenseNote;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\ExpenseType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use DateTimeImmutable;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = (new User())->initialize(
            "John",
            "Doe",
            "john.doe@example.com",
            new DateTimeImmutable('1990-01-01'),
            $this->passwordHasher->hashPassword(new User(), 'securepassword'),
            ['ROLE_USER']
        );

        $user2 = (new User())->initialize(
            "Jane",
            "Doe",
            "jane.doe@example.com",
            new DateTimeImmutable('1992-06-15'),
            $this->passwordHasher->hashPassword(new User(), 'securepassword'),
            ['ROLE_USER']
        );

        $manager->persist($user1);
        $manager->persist($user2);

        $company1 = new Company("Tech Corp");
        $company2 = new Company("Biz Solutions");

        $manager->persist($company1);
        $manager->persist($company2);

        $user1->addCompany($company1);
        $user1->addCompany($company2);

        $user2->addCompany($company2);

        $expense1 = new ExpenseNote(
            new DateTimeImmutable(),
            new Amount(75.50),
            new ExpenseType('meal'),
            $user1,
            $company1
        );

        $expense2 = new ExpenseNote(
            new DateTimeImmutable(),
            new Amount(30.00),
            new ExpenseType('fuel'),
            $user1,
            $company2
        );

        $expense3 = new ExpenseNote(
            new DateTimeImmutable(),
            new Amount(50.00),
            new ExpenseType('toll'),
            $user2,
            $company2
        );

        $manager->persist($expense1);
        $manager->persist($expense2);
        $manager->persist($expense3);

        $manager->flush();
    }
}
