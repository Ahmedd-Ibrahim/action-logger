<?php

return [
    'batches' => [
        'user_registration' => 'تم إنشاء مستخدم جديد مع :profile_count ملف شخصي و :subscription_count اشتراك',
        'user_update' => 'تم تحديث معلومات المستخدم والسجلات ذات الصلة',
        'user_deletion' => 'تم حذف المستخدم والسجلات المرتبطة',
        'batch_import' => 'تم استيراد :count سجل',
        'batch_export' => 'تم تصدير :count سجل',
    ],
    'models' => [
        'user' => 'مستخدم',
        'profile' => 'ملف شخصي',
        'subscription' => 'اشتراك',
        'role' => 'دور',
        'permission' => 'صلاحية',
    ],
    'attributes' => [
        'user' => [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة المرور',
            'status' => 'الحالة',
            'last_login_at' => 'آخر تسجيل دخول',
        ],
        'profile' => [
            'first_name' => 'الاسم الأول',
            'last_name' => 'اسم العائلة',
            'phone' => 'رقم الهاتف',
            'address' => 'العنوان',
        ],
        'subscription' => [
            'plan' => 'خطة الاشتراك',
            'start_date' => 'تاريخ البدء',
            'end_date' => 'تاريخ الانتهاء',
            'status' => 'الحالة',
        ],
    ],
]; 