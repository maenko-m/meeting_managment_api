<?php

namespace App\Entity;

use App\Repository\OfficeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OfficeRepository::class)]
class Office
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $time_zone = null;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(name: 'organization_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Organization $organization = null;

    #[ORM\OneToMany(targetEntity: MeetingRoom::class, mappedBy: 'office')]
    private Collection $meetingRooms;

    public function __construct()
    {
        $this->meetingRooms = new ArrayCollection();
    }

    public function getMeetingRooms(): Collection
    {
        return $this->meetingRooms;
    }

    public function addMeetingRoom(MeetingRoom $meetingRoom): static
    {
        if (!$this->meetingRooms->contains($meetingRoom)) {
            $this->meetingRooms[] = $meetingRoom;
            $meetingRoom->setOffice($this);
        }

        return $this;
    }

    public function removeMeetingRoom(MeetingRoom $meetingRoom): static
    {
        if ($this->meetingRooms->removeElement($meetingRoom)) {
            if ($meetingRoom->getOffice() === $this) {
                $meetingRoom->setOffice(null);
            }
        }

        return $this;
    }

    public function getTimeZone(): ?int
    {
        return $this->time_zone;
    }

    public function setTimeZone(?int $time_zone): static
    {
        $this->time_zone = $time_zone;

        return $this;
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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

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
}
