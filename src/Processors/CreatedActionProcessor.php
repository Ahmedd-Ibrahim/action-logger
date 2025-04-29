<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class CreatedActionProcessor extends BatchActionProcessor
{
    public function process(): array
    {
        $result = parent::process();
        
        // Add created-specific information
        foreach ($result['batches'] as &$batch) {
            // Add created-specific metadata
            $batch['creation_metadata'] = $this->extractCreationMetadata($batch['entities'], $batch['changes']);
        }
        
        return $result;
    }

    /**
     * Extract metadata about the created entities
     */
    protected function extractCreationMetadata(array $entities, array $changes): array
    {
        $metadata = [
            'total_created' => count($entities),
            'created_types' => [],
            'created_ids' => [],
            'initial_values' => [],
        ];
        
        foreach ($entities as $entity) {
            $metadata['created_types'][] = $entity['type'];
            $metadata['created_ids'][] = $entity['id'];
        }
        
        // Extract initial values from changes
        foreach ($changes as $change) {
            foreach ($change['changes'] as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at' && $key !== 'updated_at') {
                    $metadata['initial_values'][$key] = $value['to'];
                }
            }
        }
        
        $metadata['created_types'] = array_unique($metadata['created_types']);
        
        return $metadata;
    }
} 