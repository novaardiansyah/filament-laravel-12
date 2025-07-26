<?php

use App\Filament\Resources\ActivityLogResource;

return [
  'datetime_format' => 'd/m/Y H:i',
  'date_format' => 'd/m/Y',

  'activity_resource' => ActivityLogResource::class,
  'scoped_to_tenant' => true,
  'navigation_sort' => 10,

  'resources' => [
    'enabled' => true,
    'log_name' => 'Resource',
    'logger' => \Z3d0X\FilamentLogger\Loggers\ResourceLogger::class,
    'color' => 'success',

    'exclude' => [
      //App\Filament\Resources\UserResource::class,
    ],
    'cluster' => null,
    'navigation_group' => 'Audit Logs',
  ],

  'access' => [
    'enabled' => true,
    'logger' => \Z3d0X\FilamentLogger\Loggers\AccessLogger::class,
    'color' => 'danger',
    'log_name' => 'Access',
  ],

  'notifications' => [
    'enabled' => true,
    'logger' => \Z3d0X\FilamentLogger\Loggers\NotificationLogger::class,
    'color' => 'primary',
    'log_name' => 'Notification',
  ],

  'models' => [
    'enabled' => true,
    'log_name' => 'Model',
    'color' => 'warning',
    'logger' => \Z3d0X\FilamentLogger\Loggers\ModelLogger::class,
    'register' => [
      //App\Models\User::class,
    ],
  ],

  'custom' => [
    [
      'log_name' => 'Schedule',
      'color'    => 'info',
    ]
  ],
];
