<?php

namespace App\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private ?UuidInterface $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $firstName;

    #[ORM\Column(type: "string", length: 255)]
    private string $lastName;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: "date_immutable")]
    private DateTimeImmutable $birthDate;

    #[ORM\Column(type: "string")]
    private string $password;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: "users")]
    #[ORM\JoinTable(name: "users_companies")]
    private Collection $companies;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }

    public function initialize(
        string            $firstName,
        string            $lastName,
        string            $email,
        DateTimeImmutable $birthDate,
        string            $password,
        array             $roles = ['ROLE_USER']
    ): self
    {
        $this->id = Uuid::uuid4();
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->birthDate = $birthDate;
        $this->password = $password;
        $this->roles = $roles;
        $this->companies = new ArrayCollection();

        return $this;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getBirthDate(): DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): void
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }
    }

    public function removeCompany(Company $company): void
    {
        $this->companies->removeElement($company);
    }

    /**
     * Nouvelle méthode requise par UserInterface depuis Symfony 5.3
     * Retourne l’identifiant unique de l’utilisateur (l’email ici)
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
