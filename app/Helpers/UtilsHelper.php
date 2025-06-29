<?php

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

function carbonTranslatedFormat(string $date, string $format = 'd/m/Y H:i'): string
{
  return Carbon::parse($date)->translatedFormat($format);
}

function makePdf(\Mpdf\Mpdf $mpdf, string $filename, Model $user): bool
{
  $extension                = 'pdf';
  $directory                = 'filament-pdf';
  $filenameWithoutExtension = Uuid::uuid4() . "-{$filename}";
  $filename                 = "{$filenameWithoutExtension}.{$extension}";
  $filepath                 = "{$directory}/{$filename}";

  $mpdf->Output(storage_path("app/{$filepath}"), 'F');

  $fileUrl = URL::temporarySignedRoute(
    'download',
    now()->addHours(24),
    ['path' => $filenameWithoutExtension, 'extension' => $extension, 'directory' => $directory]
  );

  Notification::make()
    ->title('Cetak PDF Selesai')
    ->body('File Anda siap untuk diunduh.')
    ->icon('heroicon-o-arrow-down-tray')
    ->iconColor('success')
    ->actions([
      Action::make('download')
        ->label('Unduh')
        ->url($fileUrl)
        ->openUrlInNewTab()
        ->markAsRead()
        ->button()
    ])
    ->sendToDatabase($user);

  return true;
}