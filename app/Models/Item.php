<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
  use SoftDeletes;
  protected $guarded = ['id'];

  public function type(): BelongsTo
  {
    return $this->belongsTo(ItemType::class,  'type_id');
  }

  public function payments(): BelongsToMany
  {
    return $this->belongsToMany(Payment::class, 'payment_item')->withPivot(['item_code', 'quantity', 'price', 'total'])->withTimestamps();
  }
}
