<?php

namespace BIM\ActionLogger\Enums;

use BIM\ActionLogger\Contracts\ActionInterface;

enum Action: string implements ActionInterface
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';

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