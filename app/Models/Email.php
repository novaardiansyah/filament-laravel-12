<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
  use SoftDeletes;
  
  protected $table = 'emails';
  protected $guarded = ['id'];
  protected $casts = [
    'attachments' => 'array',
  ];

  public function email_log(): BelongsTo
  {
    return $this->belongsTo(EmailLog::class);
  }
}
