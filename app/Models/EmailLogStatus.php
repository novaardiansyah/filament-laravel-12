<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailLogStatus extends Model
{
  use SoftDeletes;
  protected $table = 'email_log_statuses';
  protected $guarded = ['id'];

  public function emailLogs(): HasMany
  {
    return $this->hasMany(EmailLog::class, 'status_id');
  }
}
