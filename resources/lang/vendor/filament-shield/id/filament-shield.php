<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Nama',
    'column.guard_name' => 'Nama Guard',
    'column.roles' => 'Role',
    'column.permissions' => 'Hak Akses',
    'column.updated_at' => 'Diubah pada',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Nama',
    'field.guard_name' => 'Nama Guard',
    'field.permissions' => 'Hak Akses',
    'field.select_all.name' => 'Pilih Semua',
    'field.select_all.message' => 'Aktifkan semua izin yang <span class="text-primary font-medium">Tersedia</span> untuk Role ini.',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Hak Akses',
    'nav.role.label' => 'Role',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'Role',
    'resource.label.roles' => 'Role',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'Entitas',
    'resources' => 'Resource',
    'widgets' => 'Widget',
    'pages' => 'Page',
    'custom' => 'Hak Akses Kustom',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'Kamu tidak punya izin akses',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Lihat',
        'view_any' => 'Lihat Apa Saja',
        'create' => 'Buat',
        'update' => 'Perbarui',
        'delete' => 'Hapus',
        'delete_any' => 'Hapus Apa Saja',
        'force_delete' => 'Paksa Hapus',
        'force_delete_any' => 'Paksa Hapus Apa Saja',
        'restore' => 'Pulihkan',
        'replicate' => 'Replikasi',
        'reorder' => 'Susun Ulang',
        'restore_any' => 'Pulihkan Apa Saja',
    ],
];
