<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class DeletedActionProcessor extends BatchActionProcessor
{
    public function process(): array
    {
        $result = parent::process();
        
        // Add deleted-specific information
        foreach ($result['batches'] as &$batch) {
            // Add deleted-specific message if not already set
            if (!isset($batch['message'])) {
                $batch['message'] = $this->constructDeletedMessage($batch['entities']);
            }
            
            // Add deletion metadata
            $batch['deletion_metadata'] = $this->extractDeletionMetadata($batch['entities']);
        }
        
        return $result;
    }

    /**
     * Construct a message specific to deleted actions
     */
    protected function constructDeletedMessage(array $entities): string
    {
        if (empty($entities)) {
            return 'No entities deleted';
        }

        $messageParts = [];
        
        // Get the main entity
        $mainEntity = $entities[0] ?? null;
        if ($mainEntity) {
            $entityName = Lang::get('action-logger::models.'.strtolower(class_basename($mainEntity['type'])));
            $messageParts[] = $entityName;
            
            // Add entity identifier if available
            if (isset($mainEntity['data']['id'])) {
                $messageParts[] = '#'.$mainEntity['data']['id'];
            }
        }
        
        $messageParts[] = 'has been deleted';
        
        return implode(' ', $messageParts);
    }

    /**
     * Extract metadata about the deleted entities
     */
    protected function extractDeletionMetadata(array $entities): array
    {
        $metadata = [
            'total_deleted' => count($entities),
            'deleted_types' => [],
            'deleted_ids' => [],
        ];
        
        foreach ($entities as $entity) {
            $metadata['deleted_types'][] = $entity['type'];
            $metadata['deleted_ids'][] = $entity['id'];
        }
        
        $metadata['deleted_types'] = array_unique($metadata['deleted_types']);
        
        return $metadata;
    }
} 