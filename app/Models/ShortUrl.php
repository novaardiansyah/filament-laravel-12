<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrl extends Model
{
  protected $table = 'short_urls';
  protected $guarded = ['id'];
  protected $casts = [
    'is_active' => 'boolean',
    'clicks'    => 'integer',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
