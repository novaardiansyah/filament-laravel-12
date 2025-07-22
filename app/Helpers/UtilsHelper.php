<?php

use App\Models\ActivityLog;
use App\Models\Generate;
use App\Models\ScheduledFileDeletion;
use App\Models\User;
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

function toIndonesianCurrency(float $number = 0, int $precision = 0, string $currency = 'Rp', bool $showCurrency = true)
{
  $result = 0;

  if ($number < 0) {
    $result = '-' . $currency . number_format(abs($number), $precision, ',', '.');
  } else {
    $result = $currency . number_format($number, $precision, ',', '.');
  }

  if ($showCurrency) return $result;

  $replace = str_replace(range(0, 9), '-', $result);
  return $replace;
}

function makePdf(\Mpdf\Mpdf $mpdf, string $name, ?Model $user = null, $preview = false, $notification = true, $auto_close_tbody = true): array
{
  $user ??= User::find(1); // ! Default user if not provided

  $extension                = 'pdf';
  $directory                = 'filament-pdf';
  $filenameWithoutExtension = Uuid::uuid4() . "-{$name}";
  $filename                 = "{$filenameWithoutExtension}.{$extension}";
  $filepath                 = "{$directory}/{$filename}";

  $end_tbody = $auto_close_tbody ? '</tbody><tfoot><tr></tr></tfoot>' : '';

  $mpdf->WriteHTML($end_tbody . '
        </table>
      </body>
    </html>
  ');
  
  $mpdf->SetHTMLFooter(view('layout.footer')->render());
  
  if ($preview) {
    $mpdf->Output('', 'I'); // ! Output to browser for preview
    return [
      'filename'   => $filename,
      'filepath'   => $filepath,
      'signed_url' => null, // No signed URL for preview
    ];
  }

  $mpdf->Output(storage_path("app/{$filepath}"), 'F');

  $expiration = now()->addHours(24);

  $fileUrl = URL::temporarySignedRoute(
    'download',
    $expiration,
    ['path' => $filenameWithoutExtension, 'extension' => $extension, 'directory' => $directory]
  );

  if ($notification) {
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
  }

  ScheduledFileDeletion::create([
    'user_id'                 => $user->id,
    'file_name'               => $filename,
    'file_path'               => $filepath,
    'download_url'            => $fileUrl,
    'scheduled_deletion_time' => $expiration,
  ]);

  $properties = [
    'filename'   => $filename,
    'filepath'   => $filepath,
    'signed_url' => $fileUrl,
  ];

  ActivityLog::create([
    'log_name'    => 'Export',
    'description' => "{$user->name} Export {$name}.{$extension}",
    'event'       => 'Export PDF',
    'causer_type' => 'App\Models\User',
    'causer_id'   => $user->id,
    'properties'  => $properties
  ]);

  return $properties;
}

function getCode(int $id, bool $isNotPreview = true)
{
  $genn = Generate::find($id);
  if (!$genn) return 'ER00001';

  $date = now()->translatedFormat('ymd');
  $separator = Carbon::createFromFormat('ymd', $genn->separator)->translatedFormat('ymd');

  if ($genn->queue == 9999 || (substr($date, 0, 4) != substr($separator, 0, 4))) {
    $genn->queue = 1;
    $genn->separator = $date;
  }

  $queue = substr($date, 0, 4) . str_pad($genn->queue, 4, '0', STR_PAD_LEFT) . substr($date, 4, 2);

  if ($genn->prefix) $queue = $genn->prefix . $queue;
  if ($genn->suffix) $queue .= $genn->suffix;

  if ($isNotPreview) {
    $genn->queue += 1;
    $genn->save();
  } else {
    if ($genn->isDirty()) {
      $genn->save();
    }
  }

  return $queue;
}

function getOptionMonths($short = false): array
{
  if ($short) {
    return [
      '01' => 'Jan',
      '02' => 'Feb',
      '03' => 'Mar',
      '04' => 'Apr',
      '05' => 'Mei',
      '06' => 'Jun',
      '07' => 'Jul',
      '08' => 'Agu',
      '09' => 'Sep',
      '10' => 'Okt',
      '11' => 'Nov',
      '12' => 'Des',
    ];
  }

  return [
    '1'  => 'Januari',
    '2'  => 'Februari',
    '3'  => 'Maret',
    '4'  => 'April',
    '5'  => 'Mei',
    '6'  => 'Juni',
    '7'  => 'Juli',
    '8'  => 'Agustus',
    '9'  => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember',
  ];
}

function textCapitalize($text)
{
  return ucwords(strtolower($text));
}

function textUpper($text)
{
  return strtoupper($text);
}

function textLower($text)
{
  return strtolower($text);
}