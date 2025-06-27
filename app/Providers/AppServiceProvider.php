<?php

namespace App\Providers;

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

      return URL::temporarySignedRoute(
        'download',
        now()->addHours(2),
        ['path' => $filenameWithoutExtension, 'extension' => $extension, 'directory' => 'filament-excel']
      );
    });
  }
}
