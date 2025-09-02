<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: "text", nullable: true)]
    public ?string $description = null;
    #[ORM\Column(type: "integer")]
    private int $status = 0;
    #[ORM\Column(length: 20, nullable: true)]
    private int $durationDay ;
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $timeStart = null;
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $timeEnd = null;
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $createdAt = null;
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getDurationDay(): int
    {
        return $this->durationDay;
    }

    public function setDurationDay(int $durationDay): void
    {
        $this->durationDay = $durationDay;
    }

    public function getTimeStart(): ?\DateTimeInterface
    {
        return $this->timeStart;
    }

    public function setTimeStart(?\DateTimeInterface $timeStart): void
    {
        $this->timeStart = $timeStart;
    }

    public function getTimeEnd(): ?\DateTimeInterface
    {
        return $this->timeEnd;
    }

    public function setTimeEnd(?\DateTimeInterface $timeEnd): void
    {
        $this->timeEnd = $timeEnd;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function updateStatus(?\DateTimeInterface $now = null): void
    {
        $now = $now ?? new \DateTimeImmutable('now');

        if ($this->getTimeStart() !== null && $this->getTimeEnd() !== null) {
            if ($this->getTimeStart() <= $now && $this->getTimeEnd() >= $now) {
                $this->setStatus(1);
            } elseif ($this->getTimeEnd() < $now) {
                $this->setStatus(2);
            } else {
                $this->setStatus(0);
            }
        } else {
            $this->setStatus(0);
        }
    }


}
