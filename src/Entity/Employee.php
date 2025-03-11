<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use OpenApi\Attributes as OA;
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Ignore]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Ignore]
    #[ORM\Column(length: 255)]
    private ?string $surname = null;

    #[Ignore]
    #[ORM\Column(length: 255)]
    private ?string $patronymic = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[Ignore]
    #[ORM\Column(length: 512)]
    private ?string $password = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Organization $organization = null;

    #[Ignore]
    #[ORM\ManyToMany(targetEntity: Event::class, mappedBy: 'employees')]
    private Collection $events;

    #[Ignore]
    #[ORM\Column(type: "json")]
    private array $roles = [];

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    public function setPatronymic(string $patronymic): static
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function getFullName(): string
    {
        return "{$this->name} {$this->surname} {$this->patronymic}";
    }


    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function eraseCredentials(): void {}

    #[Ignore]
    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
