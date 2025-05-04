<?php

namespace BIM\ActionLogger\Contracts;

use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * Interface for activity processors
 * 
 * Action processors are responsible for processing activity logs 
 * and generating human-readable messages.
 */
interface ActionProcessorInterface
{
    /**
     * Create a new processor instance with the given activities
     * 
     * @param Collection $activities Collection of activity logs
     */
    public function __construct(Collection $activities);

    /**
     * Process the activities and return the processed data
     * 
     * @return array Processed activity data
     */
    public function process(): array;

    /**
     * Get the processor's supported event types
     * 
     * @return array List of supported event types
     */
    public static function getSupportedEvents(): array;

    /**
     * Check if the processor supports the given event type
     * 
     * @param string $eventType The event type to check
     * @return bool Whether this processor supports the given event type
     */
    public static function supportsEvent(string $eventType): bool;

    /**
     * Clear the processed data cache
     */
    public function clearCache(): void;

    /**
     * Process activities in a specific batch
     * 
     * @param string|null $batchUuid The batch UUID to process (null for all)
     * @return array Processed batch data
     */
    public function processBatch(?string $batchUuid = null): array;
    
    /**
     * Format a human-readable message for a batch of activities
     * 
     * @param array $batchData The processed batch data
     * @return string Formatted message
     */
    public function formatBatchMessage(array $batchData): string;
} 