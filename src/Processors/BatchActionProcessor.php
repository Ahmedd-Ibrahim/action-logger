<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use BIM\ActionLogger\Contracts\ActionProcessorInterface;

class BatchActionProcessor extends BaseActionProcessor implements ActionProcessorInterface
{
    /**
     * The batch type
     */
    protected ?string $batchType = null;

    /**
     * The supported action types
     */
    protected static array $supportedActions = ['batch', 'updated', 'created', 'deleted'];

    /**
     * The processor priority
     */
    protected static int $priority = 0;

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

    /**
     * Set the batch type
     */
    public function setBatchType(string $type): self
    {
        $this->batchType = $type;
        return $this;
    }

    /**
     * Get the current batch type
     */
    public function getBatchType(): ?string
    {
        return $this->batchType;
    }

    /**
     * Process the activities and return the processed data
     */
    protected function processActivities(): array
    {
        // Group activities by batch UUID
        $groupedByBatch = $this->activities->groupBy('batch_uuid');
        
        $result = [];
        
        foreach ($groupedByBatch as $batchUuid => $batchActivities) {
            // Process each batch
            $batchData = $this->processBatchGroup($batchUuid ?: null);
            
            if (!empty($batchData)) {
                $result[] = $batchData;
            }
        }
        
        return $result;
    }

    /**
     * Process a batch of activities by batch uuid
     */
    protected function processBatchGroup(?string $batchUuid = null): array
    {
        // Get activities for this batch
        $batchActivities = $batchUuid
            ? $this->getActivities()->where('batch_uuid', $batchUuid)
            : $this->getActivities();
            
        if ($batchActivities->isEmpty()) {
            return [];
        }
        
        // Get the primary activity and common data
        $primaryActivity = $batchActivities->first();
        $commonAction = $this->getCommonAction($batchActivities);
        
        // Extract entities with their changes
        $entitiesWithChanges = $this->extractEntitiesWithChanges($batchActivities);
        $entityCount = count($entitiesWithChanges);
        
        // Generate message
        $shortMessage = Lang::get('activities.batch_message', [
            'causer' => $primaryActivity->causer ? $this->getCauserName($primaryActivity->causer) : 'System',
            'action' => $commonAction,
            'count' => $entityCount,
        ]);
        
        // Build simplified entity information
        $entities = [];
        foreach ($entitiesWithChanges as $entity) {
            // Include all entities regardless of action or changes
            // Get translated model name
            $modelType = $entity['type'];
            $modelBaseName = class_basename($modelType);
            $modelKey = $this->translateModelKey($modelType);
            $translatedModelName = Lang::has("activities.models.{$modelKey}") 
                ? Lang::get("activities.models.{$modelKey}") 
                : $modelBaseName;
                
            $entities[] = [
                'type' => $translatedModelName,
                'id' => $entity['id'],
                'action' => $entity['event'],
                'changes' => $this->simplifyChanges($entity['formatted_changes'] ?? [])
            ];
        }
        
        return [
            'batch_uuid' => $batchUuid,
            'message' => $shortMessage,
            'causer' => $primaryActivity->causer,
            'causer_type' => $primaryActivity->causer_type,
            'causer_id' => $primaryActivity->causer_id,
            'action' => $commonAction,
            'entities' => $entities,
            'created_at' => $primaryActivity->created_at,
            'entity_count' => $entityCount
        ];
    }

    /**
     * Generate a batch message for specific model type
     */
    protected function generateBatchMessage(
        Activity $primaryActivity,
        ?string $commonModelType,
        string $commonAction,
        array $entities
    ): string {
        // Get causer name
        $causerName = 'System';
        if ($primaryActivity->causer) {
            $causerName = $this->getCauserName($primaryActivity->causer);
        }

        // Get translated model name
        $modelName = 'entity';
        if ($commonModelType) {
            $modelName = $this->translateModelName($commonModelType);
        }
        
        // Get translated action
        $action = $this->translateAction($commonAction);
        
        // Get entity IDs
        $entityIds = collect($entities)->pluck('id');
        
        // Build the message based on entity count
        if ($entityIds->count() === 1) {
            // Single entity
            $id = $entityIds->first();
            return "{$causerName} {$action} {$modelName} #{$id}";
        } elseif ($entityIds->count() > 1) {
            // Multiple entities
            $count = $entityIds->count();
            return "{$causerName} {$action} {$count} {$modelName}" . ($count > 1 ? 's' : '');
        } else {
            // No entities (should not happen)
            return "{$causerName} {$action} {$modelName}";
        }
    }
    
    /**
     * Format changes with translations for a specific model
     * 
     * @param string $modelType The model class name
     * @param object $model The model instance
     * @param array $changes The changes to format
     * @return array The formatted changes
     */
    protected function formatChangesWithTranslations(string $modelType, object $model, array $changes): array
    {
        $formatted = parent::formatChangesWithTranslations($modelType, $model, $changes);
        
        // Add additional formatting if needed for batch processor
        foreach ($formatted as &$change) {
            // Format for specific model types if needed
            if (method_exists($model, 'formatChangeForDisplay')) {
                $formattedChange = $model->formatChangeForDisplay(
                    $change['attribute'],
                    $change['old_value'],
                    $change['new_value']
                );
                
                if ($formattedChange) {
                    $change = array_merge($change, $formattedChange);
                }
            }
        }
        
        return $formatted;
    }

    /**
     * Translate a model name
     */
    protected function translateModelName(string $modelType): string
    {
        $key = 'activity.models.' . strtolower(class_basename($modelType));
        return Lang::has($key) ? Lang::get($key) : class_basename($modelType);
    }

    /**
     * Translate model attributes
     */
    protected function translateAttributes(string $modelType, array $attributes): array
    {
        $translated = [];
        $modelKey = strtolower(class_basename($modelType));
        
        foreach ($attributes as $key => $value) {
            $translationKey = "activity.attributes.{$modelKey}.{$key}";
            $translated[$key] = [
                'label' => Lang::has($translationKey) ? Lang::get($translationKey) : $key,
                'value' => $value
            ];
        }
        
        return $translated;
    }

    /**
     * Determine the action performed on the activities
     */
    protected function determineAction(Collection $activities): string
    {
        $actions = $activities->pluck('event')->unique();
        if ($actions->count() === 1) {
            return $actions->first();
        }
        return 'modified';
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
     * Set a custom attribute formatter
     */
    public function setAttributeFormatter(callable $formatter): self
    {
        $this->attributeFormatter = $formatter;
        return $this;
    }

    /**
     * Set a custom translator
     */
    public function setCustomTranslator(callable $translator): self
    {
        $this->customTranslator = $translator;
        return $this;
    }

    /**
     * Process entities with their changes
     */
    protected function processEntitiesWithChanges(array $activities): array
    {
        $entities = [];
        $modelInstances = [];
        
        foreach ($activities as $activity) {
            if (!isset($activity['subject_type'], $activity['subject_id'])) continue;

            $entityKey = $activity['subject_type'] . '_' . $activity['subject_id'];
            
            if (!isset($entities[$entityKey])) {
                if (!isset($modelInstances[$entityKey])) {
                    $modelInstances[$entityKey] = $this->createModelInstance(
                        $activity['subject_type'],
                        $activity['properties']['attributes'] ?? [],
                        $activity['properties']['old'] ?? []
                    );
                }
                
                if ($modelInstances[$entityKey]) {
                    $model = $modelInstances[$entityKey];
                    $entities[$entityKey] = [
                        'type' => $activity['subject_type'],
                        'id' => $activity['subject_id'],
                        'label' => $this->getEntityLabel($model, $activity['subject_id']),
                        'changes' => [],
                    ];
                }
            }
            
            if (isset($activity['properties']['attributes'], $activity['properties']['old'])) {
                $changes = $this->getChangesWithCasts(
                    $modelInstances[$entityKey],
                    $activity['properties']['attributes'],
                    $activity['properties']['old']
                );
                
                if (!empty($changes)) {
                    $entities[$entityKey]['changes'][] = [
                        'activity_id' => $activity['id'],
                        'changes' => $this->formatBatchChanges($changes),
                    ];
                }
            }
        }
        
        return array_values($entities);
    }

    /**
     * Create a model instance with properties
     */
    protected function createModelInstance(string $modelType, array $attributes = [], array $old = []): ?object
    {
        try {
            $model = new $modelType();
            $model->setRawAttributes($attributes);
            return $model;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the entity label
     */
    protected function getEntityLabel($model, $id): string
    {
        $type = class_basename($model);
        $entityName = Lang::get('validation.attributes.' . strtolower($type));
        return "{$entityName} #{$id}";
    }

    /**
     * Get the changes between old and new values with proper casts
     */
    protected function getChangesWithCasts($model, array $new, array $old): array
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

    /**
     * Format the changes with translated and formatted attributes
     * 
     * @param array $changes The changes to format
     * @return array Formatted changes
     */
    protected function formatBatchChanges(array $changes): array
    {
        $formattedChanges = [];
        foreach ($changes as $key => $change) {
            $formattedChanges[] = [
                'key' => $key,
                'label' => $this->getTranslatedAttribute($key),
                'old_value' => $change['from'],
                'new_value' => $change['to'],
            ];
        }
        return $formattedChanges;
    }

    /**
     * Format an attribute value using the custom formatter if available
     * 
     * @param string $key The attribute key
     * @param mixed $value The attribute value
     * @return mixed The formatted value
     */
    protected function formatBatchAttributeValue(string $key, $value)
    {
        if ($this->attributeFormatter) {
            return ($this->attributeFormatter)($key, $value);
        }
        
        return $value;
    }

    /**
     * Get the translated attribute name
     */
    protected function getTranslatedAttribute(string $key): string
    {
        $translationKey = "{$this->fallbackAttributeTranslationPrefix}.{$key}";
        return Lang::has($translationKey) ? Lang::get($translationKey) : $key;
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
                // Create a model instance to handle casts
                $model = $this->createModelInstance($activity['subject_type']);
                
                if ($model) {
                    $subjectChanges = $this->getChangesWithCasts($model, $properties['attributes'], $properties['old']);
                    
                    if (!empty($subjectChanges)) {
                        $changes[] = [
                            'subject_type' => $activity['subject_type'] ?? null,
                            'subject_id' => $activity['subject_id'] ?? null,
                            'changes' => $this->formatBatchChanges($subjectChanges),
                        ];
                    }
                }
            }
        }
        
        return $changes;
    }

    /**
     * Extract entities from activities
     */
    protected function extractEntities(Collection $activities): array
    {
        $entities = [];
        
        foreach ($activities as $activity) {
            if (isset($activity['subject_type']) && isset($activity['subject_id'])) {
                // Create a new instance of the model
                $model = $this->createModelInstance($activity['subject_type']);
                
                if ($model) {
                    $entities[] = [
                        'type' => $activity['subject_type'],
                        'id' => $activity['subject_id'],
                        'class_name' => get_class($model),
                    ];
                }
            }
        }
        
        return $entities;
    }

    /**
     * Get the description for an entity
     */
    protected function getEntityDescription(array $entity): string
    {
        $type = $entity['type'] ?? null;
        $id = $entity['id'] ?? null;

        if (!$type || !$id) {
            return 'Unknown entity';
        }

        $entityName = Lang::get('validation.attributes.' . strtolower(class_basename($type)));
        return "{$entityName} #{$id}";
    }
}