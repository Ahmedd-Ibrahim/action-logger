<?php

namespace BIM\ActionLogger\Traits;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

/**
 * Provides methods for handling translations of attributes and entities
 */
trait HandlesTranslations
{
    /**
     * Translation cache
     */
    protected array $translationCache = [];

    /**
     * Translate an attribute name with fallbacks
     * 
     * @param string $attribute The attribute name
     * @param string|null $modelType The model class name (optional)
     * @return string The translated attribute name
     */
    protected function translateAttribute(string $attribute, ?string $modelType = null): string
    {
        // First check the most common place - validation.attributes
        $validationKey = "validation.attributes.{$attribute}";
        if (Lang::has($validationKey)) {
            return Lang::get($validationKey);
        }
        
        // If model type is provided, check for model-specific translations
        if ($modelType) {
            $modelBaseName = class_basename($modelType);
            $modelKey = "models.{$modelBaseName}.attributes.{$attribute}";
            
            if (Lang::has($modelKey)) {
                return Lang::get($modelKey);
            }
            
            // Try with full model path
            $fullModelKey = "models." . str_replace('\\', '.', $modelType) . ".attributes.{$attribute}";
            if (Lang::has($fullModelKey)) {
                return Lang::get($fullModelKey);
            }
        }
        
        // Fallback to a humanized version of the attribute name
        return Str::title(str_replace('_', ' ', $attribute));
    }
    
    /**
     * Translate a model name with fallbacks
     * 
     * @param string $modelType The model class name
     * @return string The translated model name
     */
    protected function translateModelName(string $modelType): string
    {
        $basename = class_basename($modelType);
        
        // Check for specific model translation
        $modelKey = "models.{$basename}.name";
        if (Lang::has($modelKey)) {
            return Lang::get($modelKey);
        }
        
        // Check in validation attributes (common pattern)
        $validationKey = "validation.attributes." . Str::snake($basename);
        if (Lang::has($validationKey)) {
            return Lang::get($validationKey);
        }
        
        // Fallback to humanized class name
        return Str::title(str_replace('_', ' ', Str::snake($basename)));
    }
    
    /**
     * Translate an action type with fallbacks
     * 
     * @param string $action The action type (created, updated, etc.)
     * @return string The translated action
     */
    protected function translateAction(string $action): string
    {
        $actionKey = "activity.actions.{$action}";
        return Lang::has($actionKey) ? Lang::get($actionKey) : Str::title($action);
    }
    
    /**
     * Format a message with placeholders
     * 
     * @param string $message The message template with placeholders
     * @param array $replacements Key-value pairs for replacements
     * @return string The formatted message
     */
    protected function formatMessage(string $message, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $message = str_replace(":{$key}", $value, $message);
        }
        
        return $message;
    }
    
    /**
     * Format an attribute value for display
     *
     * @param mixed $value The attribute value
     * @param string $modelClass The model class name
     * @param string $attribute The attribute name
     * @return mixed The formatted value
     */
    protected function formatAttributeValue(mixed $value, string $modelClass, string $attribute): mixed
    {
        // Get formatter from config if available
        $formatters = config('action-logger.attribute_formatters', []);
        
        if (isset($formatters[$attribute]) && class_exists($formatters[$attribute])) {
            $formatter = new $formatters[$attribute]();
            return $formatter->format($value, $modelClass, $attribute);
        }
        
        // Handle null values
        if ($value === null) {
            return __('activities.common.null');
        }
        
        // Handle boolean values
        if (is_bool($value)) {
            return $value ? __('activities.common.true') : __('activities.common.false');
        }
        
        // Handle array and object values
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }
        
        // Return as is for other types
        return $value;
    }
    
    /**
     * Get a model key for translations
     *
     * @param string $modelClass The model class name
     * @return string The model key
     */
    protected function getModelKey(string $modelClass): string
    {
        $cacheKey = "model_key_{$modelClass}";
        
        if (isset($this->translationCache[$cacheKey])) {
            return $this->translationCache[$cacheKey];
        }
        
        // Check model translations from config
        $modelTranslations = config('action-logger.model_translations', []);
        
        if (isset($modelTranslations[$modelClass])) {
            $this->translationCache[$cacheKey] = $modelTranslations[$modelClass];
            return $this->translationCache[$cacheKey];
        }
        
        // Try to extract the model name from the class
        $modelName = class_basename($modelClass);
        $this->translationCache[$cacheKey] = Str::snake($modelName);
        
        return $this->translationCache[$cacheKey];
    }
    
    /**
     * Convert an attribute name to a human-readable format
     *
     * @param string $attribute The attribute name
     * @return string The humanized attribute name
     */
    protected function humanizeAttribute(string $attribute): string
    {
        return Str::title(str_replace('_', ' ', $attribute));
    }
} 