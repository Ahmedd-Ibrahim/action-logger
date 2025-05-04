<?php

return [
    'batches' => [
        'user_registration' => 'Created new user with :profile_count profile(s) and :subscription_count subscription(s)',
        'user_update' => 'Updated user information and related records',
        'user_deletion' => 'Deleted user and associated records',
        'batch_import' => 'Imported :count records',
        'batch_export' => 'Exported :count records',
    ],
    'models' => [
        'user' => 'User',
        'profile' => 'Profile',
        'subscription' => 'Subscription',
        'role' => 'Role',
        'permission' => 'Permission',
    ],
    'attributes' => [
        'user' => [
            'name' => 'Name',
            'email' => 'Email Address',
            'password' => 'Password',
            'status' => 'Status',
            'last_login_at' => 'Last Login',
        ],
        'profile' => [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'phone' => 'Phone Number',
            'address' => 'Address',
        ],
        'subscription' => [
            'plan' => 'Subscription Plan',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'status' => 'Status',
        ],
    ],
]; 