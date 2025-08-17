<?php

namespace App\Filament\Resources\EmailResource\Pages;

use App\Filament\Resources\EmailResource;
use App\Mail\EmailResource\SendMail;
use App\Models\Email;
use App\Models\EmailLog;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Mail;

class ListEmails extends ListRecords
{
  protected static string $resource = EmailResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('Buat Email')
        ->modalWidth(MaxWidth::ThreeExtraLarge)
        ->mutateFormDataUsing(function (array $data): array{
          $data['code'] = getCode('email');
          return $data;
        })
        ->after(function (Email $record) {
          $now = Carbon::now();

          $attachments = $record->attachments ?? [];
          foreach ($attachments as $key => $attachment) {
            $attachments[$key] = storage_path('app/public/' . $attachment);
          }

          $send = [
            'log_name'    => 'send_app_mail',
            'email'       => $record->recipient,
            'subject'     => $record->subject,
            'body'        => $record->body,
            'attachments' => $attachments,
            'created_at'  => $now,
          ];

          $mailObj = new SendMail($send);
          $message = $mailObj->render();

          $log = EmailLog::create([
            'status_id'  => 2,
            'name'       => $send['log_name'],
            'email'      => $send['email'],
            'subject'    => $send['subject'],
            'message'    => $message,
            'created_at' => $now,
            'updated_at' => $now,
          ]);

          Mail::to($send['email'])->queue(new SendMail($send));

          $record->update([
            'email_log_id' => $log->id,
          ]);
        }),
    ];
  }
}
