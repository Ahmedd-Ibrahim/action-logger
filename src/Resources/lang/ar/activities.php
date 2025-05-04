<?php

return [
    'batch_message' => ':causer :action :count كيانات',
    
    'common' => [
        'system' => 'النظام',
        'unknown' => 'غير معروف',
        'entity' => 'كيان',
        'entities' => 'كيانات',
    ],
    
    'actions' => [
        'created' => 'أنشأ',
        'updated' => 'حدّث',
        'deleted' => 'حذف',
        'modified' => 'عدّل',
        'viewed' => 'شاهد',
        'restored' => 'استعاد',
    ],
    
    'models' => [
        'user' => 'مستخدم',
        'brief' => 'ملخص',
        'aircraft' => 'طائرة',
        'flightrequest' => 'طلب رحلة',
    ],
    
    'attributes' => [
        'brief' => [
            'brief_type' => 'نوع الملخص',
            'status' => 'الحالة',
            'catering' => 'تموين',
            'jet_name' => 'اسم الطائرة',
            'jet_size_id' => 'حجم الطائرة',
            'seats_capacity' => 'سعة المقاعد',
            'registration_no' => 'رقم التسجيل',
            'baggage_capacity' => 'سعة الأمتعة',
            'flight_request_id' => 'رقم طلب الرحلة',
            'lead_passenger_id' => 'المسافر الرئيسي',
            'additional_information' => 'معلومات إضافية',
        ],
        'aircraft' => [
            'registration_number' => 'رقم التسجيل',
        ],
    ],
]; 