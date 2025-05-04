<?php

return [
    'batch_message' => ':causer :action :count entities',
    
    'common' => [
        'system' => 'System',
        'unknown' => 'Unknown',
        'entity' => 'entity',
        'entities' => 'entities',
    ],
    
    'actions' => [
        'created' => 'created',
        'updated' => 'updated',
        'deleted' => 'deleted',
        'modified' => 'modified',
        'viewed' => 'viewed',
        'restored' => 'restored',
    ],
    
    'models' => [
        'user' => 'User',
        'brief' => 'Brief',
        'aircraft' => 'Aircraft',
        'flightrequest' => 'Flight Request',
    ],
    
    'attributes' => [
        'brief' => [
            'brief_type' => 'Brief Type',
            'status' => 'Status',
            'catering' => 'Catering',
            'jet_name' => 'Jet Name',
            'jet_size_id' => 'Jet Size',
            'seats_capacity' => 'Seats Capacity',
            'registration_no' => 'Registration Number',
            'baggage_capacity' => 'Baggage Capacity',
            'flight_request_id' => 'Flight Request',
            'lead_passenger_id' => 'Lead Passenger',
            'additional_information' => 'Additional Information',
        ],
        'aircraft' => [
            'registration_number' => 'Registration Number',
        ],
    ],
]; 