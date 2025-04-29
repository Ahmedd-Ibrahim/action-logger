<?php

namespace BIM\ActionLogger\Contracts;

interface ActionInterface
{
    public function getTranslationKey(): string;
    public function getModelTranslationKey(string $model): string;
    public function value(): string;
} 