<?php

return [
    "group" => 'Pengaturan',
    "title" => 'Penyewa',
    "single" => 'Penyewa',
    "columns" => [
        "id" => 'ID',
        "name" => 'Nama',
        "unique_id" => 'ID Unik',
        "domain" => 'Domain',
        "email" => 'Email',
        "phone" => 'Telepon',
        "password" => 'Kata Sandi',
        "passwordConfirmation" => 'Konfirmasi Kata Sandi',
        "is_active" => 'Sedang Aktif',
        "created_at" => 'Dibuat Pada',
        "updated_at" => 'Diperbarui Pada',
    ],
    "actions" => [
        "view" => 'Buka Penyewa',
        "login" => 'Masuk Ke Penyewa',
        "password" => 'Ubah Kata Sandi',
        "edit" => 'Edit',
        "delete" => 'Hapus',
    ],
    "domain" => [
        "title" => 'Domain',
        "single" => 'Domain',
        "columns" => [
            "domain" => 'Domain',
            "full" => 'Domain Penuh',
        ],
    ]
];
