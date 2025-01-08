<?php

namespace App\Enum;

enum TaskStatus: string
{
    case PENDING = 'Pending';
    case IN_PROGRESS = 'In Progress';
    case COMPLETED = 'Completed';

    public static function getChoices(): array
    {
        return [
            'Pending' => self::PENDING->value,
            'In Progress' => self::IN_PROGRESS->value,
            'Completed' => self::COMPLETED->value,
        ];
    }

    public static function getValidStatuses(): array
    {
        return [
            self::PENDING->value,
            self::IN_PROGRESS->value,
            self::COMPLETED->value,
        ];
    }
}