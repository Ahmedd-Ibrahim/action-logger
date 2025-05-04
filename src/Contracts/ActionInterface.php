<?php

namespace BIM\ActionLogger\Contracts;

/**
 * Interface for action enums
 * 
 * This interface defines the contract that action enums must implement
 * to provide translation keys and values.
 */
interface ActionInterface
{
    /**
     * Get the translation key for the action
     * 
     * @return string The translation key for this action
     */
    public function getTranslationKey(): string;

    /**
     * Get the translation key for a specific model
     * 
     * @param string $model The model name or class
     * @return string The translation key for the specified model
     */
    public function getModelTranslationKey(string $model): string;

    /**
     * Get the action value
     * 
     * @return string The string value of this action
     */
    public function value(): string;
} 