<?php

namespace App\Entity;

use App\Repository\EventRepository;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;
use App\Enum\RecurrenceType;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[Ignore]
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $time_start = null;

    #[Ignore]
    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $time_end = null;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(name: 'employee_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Employee $author = null;

    #[Ignore]
    #[ORM\ManyToOne(targetEntity: MeetingRoom::class, inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'meeting_room_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?MeetingRoom $meeting_room = null;

    #[ORM\ManyToMany(targetEntity: Employee::class, inversedBy: 'events')]
    private Collection $employees;


    #[ORM\Column(type: 'string', nullable: true, enumType: RecurrenceType::class)]
    private ?RecurrenceType $recurrenceType = null;

    #[ORM\Column(nullable: true)]
    private ?int $recurrenceInterval = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $recurrenceEnd = null;

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(name: "recurrence_parent_id", referencedColumnName: "id", nullable: true, onDelete: "CASCADE")]
    private ?Event $recurrenceParent = null;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    #[Ignore]
    public function getRecurrenceType(): ?RecurrenceType
    {
        return $this->recurrenceType;
    }

    public function getRecurrenceTypeValue(): ?string
    {
        return $this->recurrenceType?->value;
    }

    public function setRecurrenceType(?RecurrenceType $recurrenceType): static
    {
        $this->recurrenceType = $recurrenceType;

        return $this;
    }

    public function getRecurrenceInterval(): ?int
    {
        return $this->recurrenceInterval;
    }

    public function setRecurrenceInterval(?int $recurrenceInterval): static
    {
        $this->recurrenceInterval = $recurrenceInterval;

        return $this;
    }

    public function getRecurrenceEnd(): ?\DateTimeInterface
    {
        return $this->recurrenceEnd;
    }

    public function setRecurrenceEnd(?\DateTimeInterface $recurrenceEnd): static
    {
        $this->recurrenceEnd = $recurrenceEnd;

        return $this;
    }

    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): void
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
        }
    }

    public function removeEmployee(Employee $employee): void
    {
        $this->employees->removeElement($employee);
    }

    public function clearEmployees(): static
    {
        $this->employees->clear();

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getTimeStart(): ?\DateTimeInterface
    {
        return $this->time_start;
    }

    public function setTimeStart(\DateTimeInterface $time_start): static
    {
        $this->time_start = $time_start;

        return $this;
    }

    public function getTimeEnd(): ?\DateTimeInterface
    {
        return $this->time_end;
    }

    public function setTimeEnd(\DateTimeInterface $time_end): static
    {
        $this->time_end = $time_end;

        return $this;
    }

    public function getAuthor(): ?Employee
    {
        return $this->author;
    }

    public function setAuthor(?Employee $author): static
    {
        $this->author = $author;

        return $this;
    }

    #[Ignore]
    public function getMeetingRoom(): ?MeetingRoom
    {
        return $this->meeting_room;
    }

    public function setMeetingRoom(?MeetingRoom $meeting_room): static
    {
        $this->meeting_room = $meeting_room;

        return $this;
    }

    public function getMeetingRoomName(): string
    {
        return $this->meeting_room->getName();
    }

    public function getMeetingRoomId(): int
    {
        return $this->meeting_room->getId();
    }

    public function getRecurrenceParent(): ?Event
    {
        return $this->recurrenceParent;
    }

    public function setRecurrenceParent(?Event $recurrenceParent): static
    {
        $this->recurrenceParent = $recurrenceParent;

        return $this;
    }

    #[Ignore]
    public function getStartDateTime(): ?DateTime
    {
        if (!$this->date || !$this->time_start) {
            return null;
        }

        $start = new DateTime(
            $this->date->format('Y-m-d') . ' ' . $this->time_start->format('H:i:s'),
            new DateTimeZone('UTC')
        );
        return $start;
    }

    #[Ignore]
    public function getEndDateTime(): ?DateTime
    {
        if (!$this->date || !$this->time_end) {
            return null;
        }

        $end = new DateTime(
            $this->date->format('Y-m-d') . ' ' . $this->time_end->format('H:i:s'),
            new DateTimeZone('UTC')
        );
        return $end;
    }
}
