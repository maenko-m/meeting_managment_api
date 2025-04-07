<?php

namespace App\Entity;

use App\Repository\PushSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PushSubscriptionRepository::class)]
class PushSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Employee::class, inversedBy: 'pushSubscriptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Employee $employee = null;

    #[ORM\Column(length: 512)]
    private ?string $endpoint = null;

    #[ORM\Column(length: 255)]
    private ?string $authToken = null;

    #[ORM\Column(length: 255)]
    private ?string $p256dhKey = null;

    public function getId(): ?int { return $this->id; }

    public function getEmployee(): ?Employee { return $this->employee; }
    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;
        return $this;
    }

    public function getEndpoint(): ?string { return $this->endpoint; }
    public function setEndpoint(string $endpoint): static
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getAuthToken(): ?string { return $this->authToken; }
    public function setAuthToken(string $authToken): static
    {
        $this->authToken = $authToken;
        return $this;
    }

    public function getP256dhKey(): ?string { return $this->p256dhKey; }
    public function setP256dhKey(string $p256dhKey): static
    {
        $this->p256dhKey = $p256dhKey;
        return $this;
    }
}
