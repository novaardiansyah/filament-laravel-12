<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailResource\Pages;
use App\Models\Email;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailResource extends Resource
{
  protected static ?string $model = Email::class;

  protected static ?string $navigationIcon = 'heroicon-o-envelope';
  protected static ?string $navigationGroup = 'Produktivitas';
  protected static ?int $navigationSort = 19;
  protected static ?string $modelLabel = 'Kirim Email';
  protected static ?string $recordTitleAttribute = 'subject';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make([
          Forms\Components\TextInput::make('recipient')
            ->label('Kepada')
            ->required()
            ->maxLength(255),
          Forms\Components\TextInput::make('subject')
            ->label('Subjek')
            ->maxLength(255),
          Forms\Components\RichEditor::make('body')
            ->label('Konten Email')
            ->maxLength(6000)
            ->disableGrammarly()
            ->disableToolbarButtons([
              'codeBlock', 'attachFiles', 'table', 'h2', 'h3'
            ]),
          Forms\Components\FileUpload::make('attachments')
            ->label('Lampiran')
            ->multiple()
            ->downloadable()
            ->openable()
            ->directory('attachments'),
          Forms\Components\Toggle::make('save_as_draft')
            ->label('Simpan sebagai draft')
            ->default(true)
        ])
        ->description('Informasi kirim email')
        ->columns(1)
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('recipient')
          ->label('Kepada')
          ->sortable()
          ->searchable()
          ->formatStateUsing(fn(string $state): string => textLower($state)),
        Tables\Columns\TextColumn::make('subject')
          ->label('Subjek')
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('has_send')
          ->label('Status')
          ->formatStateUsing(fn(bool $state): string => $state ? 'Terkirim' : 'Belum Terkirim')
          ->badge()
          ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Dibuat pada')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label('Diubah pada')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(),
      ])
      ->filters([
        //
      ])
      // ->recordAction(null)
      ->recordUrl(null)
      ->defaultSort('updated_at', 'desc')
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->color('info')
            ->mutateRecordDataUsing(function (array $data, Email $record): array {
              $data['body'] = str($record->email_log->message)->sanitizeHtml() ?? '';
              return $data;
            })
            ->slideOver(),

          Tables\Actions\Action::make('send_email')
            ->label('Kirim Email')
            ->icon('heroicon-o-envelope')
            ->color('success')
            ->visible(fn(Email $record): bool => !$record->has_send)
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pengiriman Email')
            ->action(function (Email $record) {
              $record->sendEmail();

              Notification::make()
                ->title('Pesan Terkirim')
                ->body('Pesan berhasil dikirim ke email: ' . $record->recipient)
                ->success()
                ->send();
            }),

          Tables\Actions\EditAction::make()
            ->color('primary'),

          Tables\Actions\DeleteAction::make(),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListEmails::route('/'),
      // 'create' => Pages\CreateEmail::route('/create'),
      // 'edit' => Pages\EditEmail::route('/{record}/edit'),
    ];
  }
}
