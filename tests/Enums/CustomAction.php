<?php

namespace Tests\Enums;

use BIM\ActionLogger\Contracts\ActionInterface;

enum CustomAction: string implements ActionInterface
{
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CUSTOM = 'custom';

    public function getTranslationKey(): string
    {
        return 'action-logger::messages.' . $this->value;
    }

    public function getModelTranslationKey(string $model): string
    {
        return 'action-logger::models.' . strtolower($model);
    }

    public function value(): string
    {
        return $this->value;
    }
} 