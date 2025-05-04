<?php

namespace BIM\ActionLogger\Enums;

use BIM\ActionLogger\Contracts\ActionInterface;

/**
 * Enum representing standard activity actions
 * 
 * This enum provides standard actions that can be logged in the system
 * and implements methods to get translation keys for them.
 */
enum Action: string implements ActionInterface
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELED = 'canceled';

    /**
     * Get all action values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if the given action is valid
     */
    public static function isValid(string $action): bool
    {
        return in_array($action, self::values());
    }

    /**
     * Get the translation key for this action
     * 
     * @return string The translation key
     */
    public function getTranslationKey(): string
    {
        return 'activity.actions.' . $this->value;
    }

    /**
     * Get the human-readable name for this action
     * 
     * @return string The translated action name
     */
    public function getDisplayName(): string
    {
        return trans($this->getTranslationKey());
    }

    /**
     * Get the translation key for a model
     * 
     * @param string $model The model name or class
     * @return string The translation key
     */
    public function getModelTranslationKey(string $model): string
    {
        // Convert model class to snake_case if it's a class name
        $model = class_basename($model);
        $model = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $model));
        
        return "activities.models.{$model}";
    }

    /**
     * Get the translation key for an attribute
     */
    public function getAttributeTranslationKey(string $attribute): string
    {
        return 'activities.attributes.' . strtolower($attribute);
    }

    /**
     * Get the action value
     * 
     * @return string The action value
     */
    public function value(): string
    {
        return $this->value;
    }
} 