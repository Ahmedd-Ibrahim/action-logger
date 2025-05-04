<?php

namespace BIM\ActionLogger\Contracts;

/**
 * Interface for models that can cause activities
 */
interface CauserInterface
{
    /**
     * Get the causer's unique identifier
     */
    public function getCauserId(): int|string;

    /**
     * Get the causer's display name
     * This should return a human-readable name for the causer
     */
    public function getCauserName(): string;

    /**
     * Get the causer's type/class
     * This should return the fully qualified class name of the causer
     */
    public function getCauserType(): string;
} 