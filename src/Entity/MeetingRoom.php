<?php

namespace App\Entity;

use App\Enum\Status;
use App\Repository\MeetingRoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: MeetingRoomRepository::class)]
class MeetingRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 512)]
    private ?string $description = null;

    #[Ignore]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $calendar_code = null;

    #[Ignore]
    #[ORM\Column(type: Types::JSON)]
    private array $photo_path = [];

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $size = null;

    #[ORM\Column(type: 'string', enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\ManyToOne(targetEntity: Office::class)]
    #[ORM\JoinColumn(name: 'office_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Office $office = null;

    #[Ignore]
    #[ORM\Column(type: 'boolean')]
    private bool $is_public = true;

    #[ORM\ManyToMany(targetEntity: Employee::class)]
    #[ORM\JoinTable(name: 'meeting_room_access')]
    private Collection $employees;

    #[Ignore]
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'meeting_room')]
    private Collection $events;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        $this->events->removeElement($event);

        return $this;
    }


    public function getIsPublic(): bool
    {
        return $this->is_public;
    }

    public function setIsPublic(bool $isPublic): static
    {
        $this->is_public = $isPublic;

        return $this;
    }

    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): static
    {
        if (!$this->is_public && !$this->employees->contains($employee) && $this->employees->count() < $this->size) {
            $this->employees->add($employee);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): static
    {
        if (!$this->is_public) {
            $this->employees->removeElement($employee);
        }

        return $this;
    }

    public function clearEmployees(): static
    {
        $this->employees->clear();

        return $this;
    }

    public function getCalendarCode(): string
    {
        return $this->calendar_code;
    }

    public function setCalendarCode(string $calendar_code): static
    {
        $this->calendar_code = $calendar_code;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPhotoPath(): ?array
    {
        return $this->photo_path;
    }

    public function setPhotoPath(array $photo_paths): static
    {
        $this->photo_path = $photo_paths;

        return $this;
    }

    public function addPhotoPath(string $photoPath): self
    {
        if (!in_array($photoPath, $this->photo_path, true)) {
            $this->photo_path[] = $photoPath;
        }
        return $this;
    }

    public function removePhotoPath(string $photoPath): self
    {
        $this->photo_path = array_values(array_filter(
            $this->photo_path,
            fn($path) => $path !== $photoPath
        ));
        return $this;
    }

    public function clearPhotoPaths(): static
    {
        $this->photo_path = [];

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(int $size): static
    {
        $this->size = $size;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getOffice(): ?Office
    {
        return $this->office;
    }

    public function setOffice(?Office $office): static
    {
        $this->office = $office;

        return $this;
    }

    private ?bool $access = null; //access for current user (->getUser())

    public function getAccess(): ?bool
    {
        return $this->access;
    }

    public function setAccess(bool $access): self
    {
        $this->access = $access;
        return $this;
    }
}
