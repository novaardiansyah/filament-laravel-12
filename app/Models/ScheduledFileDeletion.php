<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ScheduledFileDeletion extends Model
{
  use SoftDeletes;
  protected $table = 'scheduled_file_deletions';
  protected $guarded = ['id'];
  protected $casts = [
    'scheduled_deletion_time' => 'datetime',
    'has_been_deleted' => 'boolean',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public static function canDeleteFiles(): bool
  {
    static $condition;
    
    if ($condition === null) {
      $condition = auth()->user() && auth()->user()->can('delete_scheduled::file::deletion');
    }

    return $condition;
  }

  public function deleteFile(): void
  {
    if (empty($this->file_path)) {
      return;
    }

    foreach (['app', 'local', 'public'] as $disk) {
      if (Storage::disk($disk)->exists($this->file_path)) {
        Storage::disk($disk)->delete($this->file_path);
      }
    }

    $this->scheduled_deletion_time = null;
    $this->has_been_deleted = true;
    $this->save();
  }
};
