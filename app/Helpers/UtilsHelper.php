<?php

use App\Models\ActivityLog;
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

function makePdf(\Mpdf\Mpdf $mpdf, string $name, Model $user): bool
{
  $extension                = 'pdf';
  $directory                = 'filament-pdf';
  $filenameWithoutExtension = Uuid::uuid4() . "-{$name}";
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

  ActivityLog::create([
    'log_name'    => 'Export',
    'description' => "{$user->name} Export {$name}.{$extension}",
    'event'       => 'Export PDF',
    'causer_type' => 'App\Models\User',
    'causer_id'   => $user->id,
    'properties'  => [
      'filepath'   => $filepath,
      'signed_url' => $fileUrl,
    ]
  ]);

  return true;
}