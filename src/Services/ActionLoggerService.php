<?php

namespace BIM\ActionLogger\Services;

use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use BIM\ActionLogger\Processors\BaseActionProcessor;
use BIM\ActionLogger\Processors\BatchActionProcessor;
use Illuminate\Support\Traits\Macroable;
use BIM\ActionLogger\Processors\ProcessorFactory;
use BIM\ActionLogger\Contracts\ActionProcessorInterface;
use Spatie\Activitylog\Facades\Activity as ActivityLog;

/**
 * Main ActionLogger service
 * 
 * This service provides methods for processing activity logs and
 * generating human-readable messages for activities.
 */
class ActionLoggerService
{
    use Macroable;

    /**
     * The processor factory
     */
    protected ProcessorFactory $processorFactory;

    /**
     * Current batch UUID
     */
    protected ?string $currentBatchUuid = null;
    
    /**
     * Current batch metadata
     */
    protected array $batchMetadata = [];
    
    /**
     * Current batch type
     */
    protected ?string $currentBatchType = null;

    /**
     * Create a new ActionLogger service
     * 
     * @param ProcessorFactory $processorFactory The processor factory
     */
    public function __construct(ProcessorFactory $processorFactory)
    {
        $this->processorFactory = $processorFactory;
    }

    /**
     * Start logging activity for a subject
     */
    public function on($subjects): self
    {
        ActivityLog::on($subjects);
        return $this;
    }

    /**
     * Tag the current batch with a type
     */
    public function tagCurrentBatch(string $type): self
    {
        $this->currentBatchType = $type;
        return $this;
    }

    /**
     * Start a new batch
     *
     * @param string|null $batchUuid Custom batch UUID (will generate one if not provided)
     * @param array $metadata Additional metadata for the batch
     * @return string The batch UUID
     */
    public function startBatch(?string $batchUuid = null, array $metadata = []): string
    {
        // Generate a UUID if none provided
        $this->currentBatchUuid = $batchUuid ?: (string) Str::uuid();
        
        // Store batch metadata
        $this->batchMetadata = array_merge([
            'started_at' => now(),
            'user_id' => auth()->id(),
        ], $metadata);
        
        return $this->currentBatchUuid;
    }
    
    /**
     * Commit the current batch
     *
     * @return bool
     */
    public function commitBatch(): bool
    {
        if (!$this->currentBatchUuid) {
            return false;
        }
        
        // Update batch metadata
        $this->batchMetadata = array_merge($this->batchMetadata, [
            'completed_at' => now(),
            'status' => 'completed',
        ]);
        
        // Apply batch UUID to activities created during this batch
        $this->applyBatchToActivities();
        
        // Clear current batch
        $this->currentBatchUuid = null;
        $this->batchMetadata = [];
        
        return true;
    }
    
    /**
     * Discard the current batch
     *
     * @return bool
     */
    public function discardBatch(): bool
    {
        if (!$this->currentBatchUuid) {
            return false;
        }
        
        // Optional: Delete all activities in this batch
        if (config('action-logger.batch.delete_discarded', false)) {
            Activity::where('batch_uuid', $this->currentBatchUuid)->delete();
        }
        
        // Clear current batch
        $this->currentBatchUuid = null;
        $this->batchMetadata = [];
        
        return true;
    }
    
    /**
     * Apply the current batch UUID to recent activities that don't have a batch UUID
     */
    protected function applyBatchToActivities(): void
    {
        if (!$this->currentBatchUuid) {
            return;
        }
        
        // Get activities created since batch started without a batch UUID
        $startedAt = $this->batchMetadata['started_at'] ?? now()->subMinutes(5);
        
        // Get activities without batch UUID
        $activities = Activity::whereNull('batch_uuid')
            ->where('created_at', '>=', $startedAt)
            ->get();
            
        // Update each activity manually to ensure compatibility with all database drivers
        foreach ($activities as $activity) {
            $properties = $activity->properties ? $activity->properties->toArray() : [];
            $properties['batch_metadata'] = $this->batchMetadata;
            
            $activity->batch_uuid = $this->currentBatchUuid;
            $activity->properties = $properties;
            $activity->save();
        }
    }
    
    /**
     * Get the current batch UUID
     *
     * @return string|null
     */
    public function getCurrentBatchUuid(): ?string
    {
        return $this->currentBatchUuid;
    }
    
    /**
     * Get the current batch metadata
     *
     * @return array
     */
    public function getBatchMetadata(): array
    {
        return $this->batchMetadata;
    }
    
    /**
     * Check if a batch is currently active
     *
     * @return bool
     */
    public function hasBatch(): bool
    {
        return $this->currentBatchUuid !== null;
    }
    
    /**
     * Process activities
     *
     * @param Collection|array $activities
     * @return array
     */
    public function process($activities): array
    {
        if (!$activities instanceof Collection) {
            $activities = collect($activities);
        }
        
        if ($activities->isEmpty()) {
            return [];
        }
        
        $processor = $this->getProcessorForActivities($activities);
        return $processor->process();
    }

    /**
     * Process activities in a batch
     *
     * @param Collection|Activity $activities The activities to process
     * @param string|null $batchUuid Batch UUID
     * @return array Processed batch data
     */
    public function processBatch(Collection|Activity $activities, ?string $batchUuid = null): array
    {
        // Convert single activity to collection
        if ($activities instanceof Activity) {
            $activities = collect([$activities]);
        }
        
        // Filter out request_tracking and api_request events as they are handled by the middleware
        $activities = $activities->filter(function ($activity) {
            return !in_array($activity->event, ['request_tracking', 'api_request']);
        });
        
        // Get the processor for these activities
        $processor = $this->processorFactory->getProcessor($activities);
        
        // Process the batch
        return $processor->processBatch($batchUuid);
    }

    /**
     * Format a batch message for the given activities
     * 
     * @param Collection|Activity $activities Activities to format
     * @param array $batchData Optional pre-processed batch data
     * @return string Formatted message
     */
    public function formatBatchMessage(Collection|Activity $activities, array $batchData = []): string
    {
        $processor = $this->getProcessor($activities);
        
        if (empty($batchData)) {
            $batchData = $processor->process();
        }
        
        return $processor->formatBatchMessage($batchData);
    }

    /**
     * Get the appropriate processor for the given activities
     * 
     * @param Collection|Activity $activities Activities to process
     * @return ActionProcessorInterface The processor for the activities
     */
    protected function getProcessor(Collection|Activity $activities): ActionProcessorInterface
    {
        return $this->processorFactory->getProcessor($activities);
    }

    /**
     * Get activities with conditions
     */
    public function getActivities(array $conditions): Collection
    {
        $query = Activity::query();
        
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get activities for a batch
     */
    public function getBatchActivities(string $batchUuid): Collection
    {
        return $this->getActivities(['batch_uuid' => $batchUuid]);
    }

    /**
     * Get activities for a model
     */
    public function getModelActivities(string $modelType, int $modelId): Collection
    {
        return $this->getActivities([
            'subject_type' => $modelType,
            'subject_id' => $modelId
        ]);
    }

    /**
     * Get activities for a causer
     */
    public function getCauserActivities(string $causerType, int $causerId): Collection
    {
        return $this->getActivities([
            'causer_type' => $causerType,
            'causer_id' => $causerId
        ]);
    }

    /**
     * Get activities for a specific event
     */
    public function getEventActivities(string $event): Collection
    {
        return $this->getActivities(['event' => $event]);
    }

    /**
     * Log an action with event
     */
    public function logAction(
        $subjects,
        string $event,
        string $description,
        $causer = null,
        array $properties = [],
        ?string $logName = null
    ): void {
        $logger = ActivityLog::on($subjects)
            ->event($event)
            ->by($causer)
            ->withProperties($properties);

        if ($logName) {
            $logger->useLog($logName);
        }

        $logger->log($description);
    }
    

    /**
     * Get a query builder for activities by subject
     *
     * @param mixed $subject
     * @return object
     */
    public function forSubject($subject): object
    {
        return new class($subject) {
            protected $subject;
            
            public function __construct($subject) {
                $this->subject = $subject;
            }
            
            public function get(): Collection {
                $subjectType = get_class($this->subject);
                $subjectId = $this->subject->getKey();
                
                return Activity::where('subject_type', $subjectType)
                    ->where('subject_id', $subjectId)
                    ->latest()
                    ->get();
            }
        };
    }
    
    /**
     * Get processor for activities
     *
     * @param Collection $activities
     * @return BaseActionProcessor
     */
    protected function getProcessorForActivities(Collection $activities): BaseActionProcessor
    {
        // Get first activity to determine information
        $firstActivity = $activities->first();
        
        if (!$firstActivity) {
            return new BatchActionProcessor($activities);
        }
        
        // Try route-based processor
        $processorClass = $this->resolveProcessorFromRoute($firstActivity);
        
        if ($processorClass && class_exists($processorClass)) {
            /** @var string $processorClass */
            return $this->createProcessor($processorClass, $activities);
        }
        
        // Fall back to default processor
        $defaultProcessorClass = config('action-logger.default_processors.default') ?? BatchActionProcessor::class;
        return $this->createProcessor($defaultProcessorClass, $activities);
    }
    
    /**
     * Create processor instance
     * 
     * @param string $processorClass
     * @param Collection $activities
     * @return BaseActionProcessor
     */
    protected function createProcessor(string $processorClass, Collection $activities): BaseActionProcessor
    {
        return new $processorClass($activities);
    }
    
    /**
     * Resolve processor based on route information
     *
     * @param Activity $activity
     * @return string|null
     */
    protected function resolveProcessorFromRoute(Activity $activity): ?string
    {
        // Extract route information from batch metadata
        $properties = $activity->properties ? $activity->properties->toArray() : [];
        $batchMetadata = $properties['batch_metadata'] ?? null;
        
        if (!$batchMetadata) {
            return null;
        }
        
        // Check for route name processor
        if (isset($batchMetadata['name'])) {
            $routeName = $batchMetadata['name'];
            $routeProcessors = config('action-logger.route_processors', []);
            
            // Direct match
            if (isset($routeProcessors[$routeName])) {
                return $routeProcessors[$routeName];
            }
            
            // Pattern match
            foreach ($routeProcessors as $pattern => $processorClass) {
                if (Str::is($pattern, $routeName)) {
                    return $processorClass;
                }
            }
        }
        
        // Check for controller action processor
        if (isset($batchMetadata['controller'], $batchMetadata['action'])) {
            $controllerAction = $batchMetadata['controller'] . '@' . $batchMetadata['action'];
            $controllerProcessors = config('action-logger.controller_processors', []);
            
            if (isset($controllerProcessors[$controllerAction])) {
                return $controllerProcessors[$controllerAction];
            }
        }
        
        return null;
    }
    
    /**
     * Get all activities
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return Activity::latest()->get();
    }
}
