<?php

return [
    'group' => 'Settings',
    'title' => 'Tenants',
    'single' => 'Tenant',
    'columns' => [
        'id' => 'ID',
        'name' => 'Name',
        'unique_id' => 'Unique ID',
        'domain' => 'Domain',
        'email' => 'Email',
        'phone' => 'Phone',
        'password' => 'Password',
        'passwordConfirmation' => 'Password Confirmation',
        'is_active' => 'Is Active',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],
    'actions' => [
        'view' => 'Open Tenant',
        'login' => 'Login To Tenant',
        'password' => 'Change Password',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],
    'domains' => [
        'title' => 'Domains',
        'single' => 'Domain',
        'columns' => [
            'domain' => 'Domain',
            'full' => 'Full Domain',
        ],
    ],

];
