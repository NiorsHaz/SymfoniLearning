<?php

namespace App\Entity;

use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use App\Validator\BanWord;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\MaxEstimates;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug est déjà utilisé.')]
#[MaxEstimates()]
class Task extends AbstractDeletableEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tasks.list', 'tasks.show', 'projects.show'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 10, minMessage: 'Minimum 10 caractères')]
    #[BanWord()]
    #[Groups(['tasks.list', 'tasks.show', 'tasks.create', 'tasks.update', 'projects.show'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(min: 10, minMessage: 'Minimum 10 caractères')]
    #[Assert\Regex('/^[a-z0-9]+(?:(?:-|_)+[a-z0-9]+)*$/', message: 'Format invalide')]
    #[Groups(['tasks.list', 'tasks.show', 'tasks.update'])]
    private ?string $slug = null;

    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['tasks.create', 'tasks.update', 'tasks.show'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Assert\NotBlank(message: "Valeur invalide")]
    #[Assert\Positive(message: "L'estimation doit être supérieure à 0.")]
    #[Groups(['tasks.list', 'tasks.create', 'tasks.update', 'tasks.show'])]
    private ?int $estimates = null;

    #[ORM\Column]
    #[Groups(['tasks.show'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Groups(['tasks.show'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tasks.list', 'tasks.create', 'tasks.show', 'tasks.update'])]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['tasks.list', 'tasks.show'])]
    private ?\DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['tasks.list', 'tasks.create', 'tasks.show', 'tasks.update'])]
    private ?Project $project = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'tasks')]
    #[Groups(['tasks.list', 'tasks.show'])]
    private Collection $assignees;

    #[Groups(['tasks.list', 'tasks.show'])]
    #[ORM\Column(enumType: TaskStatus::class, nullable: true)]
    private ?TaskStatus $status = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?Category $Category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Attachments = null;

    public function __construct()
    {
        $this->assignees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEstimates(): ?int
    {
        return $this->estimates;
    }

    public function setEstimates(?int $estimates): static
    {
        $this->estimates = $estimates;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAssignees(): Collection
    {
        return $this->assignees;
    }

    public function addAssignee(User $assignee): static
    {
        if (!$this->assignees->contains($assignee)) {
            $this->assignees->add($assignee);
        }

        return $this;
    }

    public function removeAssignee(User $assignee): static
    {
        $this->assignees->removeElement($assignee);

        return $this;
    }

    public function getStatus(): ?TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->Category;
    }

    public function setCategory(?Category $Category): static
    {
        $this->Category = $Category;

        return $this;
    }

    public function getAttachments(): ?string
    {
        return $this->Attachments;
    }

    public function setAttachments(?string $Attachments): static
    {
        $this->Attachments = $Attachments;

        return $this;
    }
}
