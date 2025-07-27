<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
  protected $table = 'activity_log';
  protected $guarded = ['id'];
  protected $casts = [
    'properties'  => 'collection',
    'geolocation' => 'array',
  ];

  public function subject(): MorphTo
  {
    return $this->morphTo();
  }

  public function causer(): MorphTo
  {
    return $this->morphTo();
  }
}
