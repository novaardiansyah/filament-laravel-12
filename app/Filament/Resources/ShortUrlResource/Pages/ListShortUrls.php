<?php

namespace App\Filament\Resources\ShortUrlResource\Pages;

use App\Filament\Resources\ShortUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;
use App\Models\ShortUrl;

class ListShortUrls extends ListRecords
{
  protected static string $resource = ShortUrlResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->modalWidth(MaxWidth::TwoExtraLarge)
        ->mutateFormDataUsing(function (array $data): array {
          $data['code'] = Str::random(7);
          $data['short_url'] = config('services.tinyurl.alias_domain') . '/r/' . $data['code'];

          $exist = ShortUrl::where('code', $data['code'])->exists();

          if ($exist) {
            Notification::make()
              ->title('Error')
              ->body('Gagal membuat Short URL, silahkan coba lagi (e1).')
              ->danger()
              ->send();

            $this->halt();
          }

          $response = Http::withToken(config('services.tinyurl.token'))
            ->post(config('services.tinyurl.url') . '/create', [
              'url'    => $data['short_url'],
              'domain' => 'tinyurl.com',
            ]);

          if ($response->failed()) {
            Notification::make()
              ->title('Terjadi kesalahan pada integrasi')
              ->body('Proses tetap dilanjutkan tanpa integrasi pihak ketiga.')
              ->warning()
              ->send();
          }

          $response = $response->json();

          $data['user_id'] = auth()->id();
          $data['tiny_url'] = $response['data']['tiny_url'] ?? null;

          return $data;
        }),
    ];
  }
}
