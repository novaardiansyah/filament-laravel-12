<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLog extends Model
{
  use SoftDeletes;
  protected $table = 'user_logs';
  protected $guarded = ['id'];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
