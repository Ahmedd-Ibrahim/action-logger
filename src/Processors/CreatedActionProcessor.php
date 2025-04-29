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
            // Add created-specific message if not already set
            if (!isset($batch['message'])) {
                $batch['message'] = $this->constructCreatedMessage($batch['entities'], $batch['changes']);
            }
        }
        
        return $result;
    }

    /**
     * Construct a message specific to created actions
     */
    protected function constructCreatedMessage(array $entities, array $changes): string
    {
        if (empty($entities)) {
            return 'No entities created';
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
        
        $messageParts[] = 'has been created';
        
        // Add initial values
        foreach ($changes as $change) {
            foreach ($change['changes'] as $key => $value) {
                if ($key !== 'id' && $key !== 'created_at' && $key !== 'updated_at') {
                    $messageParts[] = 'with';
                    $messageParts[] = $value['label'];
                    $messageParts[] = $value['to'];
                }
            }
        }
        
        return implode(' ', $messageParts);
    }
} 