<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailLog extends Model
{
  use SoftDeletes;
  protected $table = 'email_logs';
  protected $guarded = ['id'];

  public function status(): BelongsTo
  {
    return $this->belongsTo(EmailLogStatus::class, 'status_id');
  }
}
