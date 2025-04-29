<?php

namespace BIM\ActionLogger\Processors;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;

class UpdatedActionProcessor extends BatchActionProcessor
{
    public function process(): array
    {
        $result = parent::process();
        
        // Add updated-specific information
        foreach ($result['batches'] as &$batch) {
            // Add updated-specific message if not already set
            if (!isset($batch['message'])) {
                $batch['message'] = $this->constructUpdatedMessage($batch['entities'], $batch['changes']);
            }
            
            // Add change statistics
            $batch['change_stats'] = $this->calculateChangeStats($batch['changes']);
        }
        
        return $result;
    }

    /**
     * Construct a message specific to updated actions
     */
    protected function constructUpdatedMessage(array $entities, array $changes): string
    {
        if (empty($entities)) {
            return 'No entities updated';
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
        
        // Add status changes first if any
        foreach ($changes as $change) {
            if (isset($change['changes']['status'])) {
                $statusChange = $change['changes']['status'];
                $messageParts[] = 'has been';
                $messageParts[] = $statusChange['to'];
                break;
            }
        }
        
        // Add other changes
        $otherChanges = [];
        foreach ($changes as $change) {
            foreach ($change['changes'] as $key => $value) {
                if ($key !== 'status' && $key !== 'updated_at') {
                    $otherChanges[] = $value['label'].' to '.$value['to'];
                }
            }
        }
        
        if (!empty($otherChanges)) {
            if (empty($messageParts)) {
                $messageParts[] = 'has been updated';
            }
            $messageParts[] = 'with';
            $messageParts[] = implode(', ', $otherChanges);
        }
        
        return implode(' ', $messageParts);
    }

    /**
     * Calculate statistics about the changes
     */
    protected function calculateChangeStats(array $changes): array
    {
        $stats = [
            'total_changes' => 0,
            'changed_fields' => [],
            'status_changed' => false,
            'changed_values' => [],
        ];
        
        foreach ($changes as $change) {
            foreach ($change['changes'] as $key => $value) {
                $stats['total_changes']++;
                $stats['changed_fields'][] = $key;
                $stats['changed_values'][$key] = [
                    'from' => $value['from'],
                    'to' => $value['to'],
                    'label' => $value['label'],
                ];
                
                if ($key === 'status') {
                    $stats['status_changed'] = true;
                }
            }
        }
        
        $stats['changed_fields'] = array_unique($stats['changed_fields']);
        
        return $stats;
    }
} 