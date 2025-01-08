<?php

namespace App\Entity;

abstract class AbstractDeletableEntity implements DeletableEntityInterface
{
    public function isDeleted(): bool {
        return null !== $this->getDeletedAt();
    }
}
