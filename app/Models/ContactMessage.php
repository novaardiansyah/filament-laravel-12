<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
  use SoftDeletes;
  protected $guarded = ['id'];
  protected $casts = [
    'is_read' => 'boolean',
  ];
}
