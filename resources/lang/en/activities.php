<?php

return [
    // Common translations
    'common' => [
        'system' => 'System',
        'unknown' => 'Unknown',
        'null' => 'None',
        'true' => 'Yes',
        'false' => 'No',
    ],
    
    // Action translations
    'actions' => [
        'created' => 'created',
        'updated' => 'updated',
        'deleted' => 'deleted',
        'restored' => 'restored',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'canceled' => 'canceled',
        'modified' => 'modified',
    ],
    
    // Generic batch message format
    'batch_message' => ':causer :action #:subject_id',
    
    // Rent request specific translations
    'rent_request' => [
        'created' => ':causer created rent request #:rent_request_id',
        'updated' => ':causer updated rent request #:rent_request_id',
        'approved' => ':causer approved rent request #:rent_request_id',
        'rejected' => ':causer rejected rent request #:rent_request_id',
        'canceled' => ':causer canceled rent request #:rent_request_id',
        'status_changed' => ':causer changed rent request #:rent_request_id status from :from_status to :to_status',
        
        // Status translations
        'statuses' => [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'canceled' => 'Canceled',
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
        ],
    ],
    
    // Car specific translations
    'car' => [
        'updated' => ':causer updated car #:car_id',
        'status_changed' => ':causer changed car #:car_id status from :from_status to :to_status',
        
        // Status translations
        'statuses' => [
            'available' => 'Available',
            'rented' => 'Rented',
            'maintenance' => 'In Maintenance',
            'reserved' => 'Reserved',
        ],
    ],
    
    // Contract specific translations
    'contract' => [
        'created' => ':causer created contract #:contract_id for rent request #:rent_request_id',
        'updated' => ':causer updated contract #:contract_id',
        'canceled' => ':causer canceled contract #:contract_id',
    ],
    
    // Payment specific translations
    'payment' => [
        'created' => ':causer recorded payment #:payment_id for contract #:contract_id',
        'updated' => ':causer updated payment #:payment_id',
        'canceled' => ':causer canceled payment #:payment_id',
    ],
]; 