<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Support\ServiceProvider;
use pxlrbt\FilamentExcel\FilamentExport;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    FilamentExport::createExportUrlUsing(function ($export) {
      $fileInfo = pathinfo($export['filename']);
      $filenameWithoutExtension = $fileInfo['filename'];
      $extension = $fileInfo['extension'];

      $directory = 'filament-excel';
      $fileName = trim(substr($filenameWithoutExtension, 37));
      $user = auth()->user();

      $fileUrl = URL::temporarySignedRoute(
        'download',
        now()->addHours(24),
        ['path' => $filenameWithoutExtension, 'extension' => $extension, 'directory' => $directory]
      );

      ActivityLog::create([
        'log_name' => 'Export',
        'description' => "{$user->name} Export {$fileName}.{$extension}",
        'event' => 'Export Excel',
        'causer_type' => 'App\Models\User',
        'causer_id' => $user->id,
        'properties' => [
          'filepath' => "{$directory}/{$filenameWithoutExtension}.{$extension}",
          'signed_url' => $fileUrl,
        ]
      ]);

      return $fileUrl;
    });
  }
}
