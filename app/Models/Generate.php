<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Generate extends Model
{
  protected $guarded = ['id'];

  public function getNextId(): string
  {
    return getCode($this->alias, false);
  }
}
