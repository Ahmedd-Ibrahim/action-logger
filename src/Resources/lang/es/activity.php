<?php

return [
    'batches' => [
        'user_registration' => 'Creado nuevo usuario con :profile_count perfil(es) y :subscription_count suscripción(es)',
        'user_update' => 'Actualizada información del usuario y registros relacionados',
        'user_deletion' => 'Eliminado usuario y registros asociados',
        'batch_import' => 'Importados :count registros',
        'batch_export' => 'Exportados :count registros',
    ],
    'models' => [
        'user' => 'Usuario',
        'profile' => 'Perfil',
        'subscription' => 'Suscripción',
        'role' => 'Rol',
        'permission' => 'Permiso',
    ],
    'attributes' => [
        'user' => [
            'name' => 'Nombre',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'status' => 'Estado',
            'last_login_at' => 'Último inicio de sesión',
        ],
        'profile' => [
            'first_name' => 'Nombre',
            'last_name' => 'Apellido',
            'phone' => 'Teléfono',
            'address' => 'Dirección',
        ],
        'subscription' => [
            'plan' => 'Plan de suscripción',
            'start_date' => 'Fecha de inicio',
            'end_date' => 'Fecha de fin',
            'status' => 'Estado',
        ],
    ],
]; 