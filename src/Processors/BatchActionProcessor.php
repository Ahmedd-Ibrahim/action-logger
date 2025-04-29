<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class BatchActionProcessor extends ActionProcessor
{
    /**
     * The translation key prefix for attributes
     */
    protected string $attributeTranslationPrefix = 'action-logger::attributes';

    /**
     * The fallback translation key prefix for attributes
     */
    protected string $fallbackAttributeTranslationPrefix = 'validation.attributes';

    /**
     * Custom attribute formatter function
     */
    protected $attributeFormatter = null;

    /**
     * Custom translation function
     */
    protected $customTranslator = null;

    public function process(): array
    {
        // Group activities by their batch UUID
        $groupedActivities = $this->activities->groupBy('batch_uuid');
        
        $processedBatches = [];
        
        foreach ($groupedActivities as $batchUuid => $activities) {
            // Get the main activity (usually the first one in the batch)
            $mainActivity = $activities->first();
            
            if (!$mainActivity) {
                continue;
            }
            
            // Process all activities in the batch
            $processedActivities = $activities->map(fn (Activity $activity) => $this->processActivity($activity));
            
            // Extract all changes and entities from activities
            $changes = $this->extractAllChanges($processedActivities);
            $entities = $this->extractEntities($processedActivities);
            
            // Construct the message from changes and entities
            $message = $this->constructMessage($changes, $entities);
            
            // Resolve action type from the main activity
            $actionType = $this->resolveActionType($mainActivity);
            
            $processedBatches[] = [
                'message' => $message,
                'changes' => $changes,
                'entities' => $entities,
                'action_type' => $actionType,
            ];
        }
        
        return [
            'batches' => $processedBatches,
            'total_batches' => count($processedBatches),
        ];
    }

    /**
     * Resolve the action type from the activity
     */
    protected function resolveActionType(Activity $activity): string
    {
        // First check if there's a custom processor for this action
        $customProcessor = config('action-logger.custom_processors.'.$activity->description);
        if ($customProcessor) {
            return $activity->description;
        }

        // Then check if it's a standard action
        $standardActions = ['created', 'updated', 'deleted'];
        if (in_array($activity->description, $standardActions)) {
            return $activity->description;
        }

        // Finally, check custom actions
        $customActions = config('action-logger.custom_actions', []);
        if (isset($customActions[$activity->description])) {
            return $activity->description;
        }

        // Default to the activity description
        return $activity->description;
    }

    /**
     * Set the attribute translation prefix
     */
    public function setAttributeTranslationPrefix(string $prefix): self
    {
        $this->attributeTranslationPrefix = $prefix;
        return $this;
    }

    /**
     * Set the fallback attribute translation prefix
     */
    public function setFallbackAttributeTranslationPrefix(string $prefix): self
    {
        $this->fallbackAttributeTranslationPrefix = $prefix;
        return $this;
    }

    /**
     * Set a custom attribute formatter function
     */
    public function setAttributeFormatter(callable $formatter): self
    {
        $this->attributeFormatter = $formatter;
        return $this;
    }

    /**
     * Set a custom translation function
     */
    public function setCustomTranslator(callable $translator): self
    {
        $this->customTranslator = $translator;
        return $this;
    }
    
    /**
     * Extract all changes from activities in a batch
     */
    protected function extractAllChanges(Collection $activities): array
    {
        $changes = [];
        
        foreach ($activities as $activity) {
            $properties = $activity['properties'] ?? [];
            
            if (isset($properties['attributes']) && isset($properties['old'])) {
                $subjectChanges = $this->getChanges($properties['attributes'], $properties['old']);
                
                if (!empty($subjectChanges)) {
                    $changes[] = [
                        'subject_type' => $activity['subject_type'] ?? null,
                        'subject_id' => $activity['subject_id'] ?? null,
                        'changes' => $this->formatChanges($subjectChanges),
                    ];
                }
            }
        }
        
        return $changes;
    }

    /**
     * Format the changes with translated and formatted attributes
     */
    protected function formatChanges(array $changes): array
    {
        $formattedChanges = [];
        
        foreach ($changes as $key => $change) {
            $formattedKey = $this->getTranslatedAttribute($key);
            $formattedChange = [
                'key' => $key,
                'label' => $formattedKey,
                'from' => $this->formatAttributeValue($key, $change['from'] ?? null),
                'to' => $this->formatAttributeValue($key, $change['to'] ?? null),
            ];
            
            $formattedChanges[$key] = $formattedChange;
        }
        
        return $formattedChanges;
    }

    /**
     * Format an attribute value using the custom formatter if available
     */
    protected function formatAttributeValue(string $key, $value)
    {
        if ($this->attributeFormatter) {
            return ($this->attributeFormatter)($key, $value);
        }
        
        return $value;
    }
    
    /**
     * Extract entities from activities
     */
    protected function extractEntities(Collection $activities): array
    {
        $entities = [];
        
        foreach ($activities as $activity) {
            if (isset($activity['subject'])) {
                $entities[] = [
                    'type' => $activity['subject_type'] ?? null,
                    'id' => $activity['subject_id'] ?? null,
                    'data' => $activity['subject'],
                ];
            }
        }
        
        return $entities;
    }
    
    /**
     * Construct a message from changes and entities
     */
    protected function constructMessage(array $changes, array $entities): string
    {
        if (empty($changes) && empty($entities)) {
            return 'No changes recorded';
        }

        $messageParts = [];
        
        // Get the main entity (usually the first one)
        $mainEntity = $entities[0] ?? null;
        if ($mainEntity) {
            $entityName = Lang::get('action-logger::models.'.strtolower(class_basename($mainEntity['type'])));
            $messageParts[] = $entityName;
            
            // Add entity identifier if available
            if (isset($mainEntity['data']['id'])) {
                $messageParts[] = '#'.$mainEntity['data']['id'];
            }
        }
        
        // Add status changes
        foreach ($changes as $change) {
            if (isset($change['changes']['status'])) {
                $statusChange = $change['changes']['status'];
                $messageParts[] = 'has been';
                $messageParts[] = $statusChange['to'];
            }
        }
        
        // Add additional changes
        foreach ($changes as $change) {
            foreach ($change['changes'] as $key => $value) {
                if ($key !== 'status') {
                    $messageParts[] = 'with';
                    $messageParts[] = $value['label'];
                    $messageParts[] = $value['to'];
                }
            }
        }
        
        return implode(' ', $messageParts);
    }

    /**
     * Get the translated attribute name
     */
    protected function getTranslatedAttribute(string $key): string
    {
        // Try custom translator first
        if ($this->customTranslator) {
            $translation = ($this->customTranslator)($key);
            if ($translation !== null) {
                return $translation;
            }
        }

        // Try the custom translation next
        $translationKey = "{$this->attributeTranslationPrefix}.{$key}";
        if (Lang::has($translationKey)) {
            return Lang::get($translationKey);
        }

        // Fallback to validation attributes
        $fallbackKey = "{$this->fallbackAttributeTranslationPrefix}.{$key}";
        if (Lang::has($fallbackKey)) {
            return Lang::get($fallbackKey);
        }

        // If no translation found, return the key
        return $key;
    }
    
    /**
     * Get the changes between old and new values
     */
    protected function getChanges(array $new, array $old): array
    {
        $changes = [];
        
        foreach ($new as $key => $value) {
            if (!isset($old[$key]) || $old[$key] !== $value) {
                $changes[$key] = [
                    'from' => $old[$key] ?? null,
                    'to' => $value
                ];
            }
        }
        
        return $changes;
    }
} 