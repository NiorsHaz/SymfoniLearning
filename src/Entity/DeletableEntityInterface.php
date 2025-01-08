<?php

namespace App\Entity;

interface DeletableEntityInterface
{
    public function getDeletedAt(): ?\DateTimeImmutable;

    public function setDeletedAt(?\DateTimeImmutable $deletedAt): static;

    public function isDeleted(): bool;
}
