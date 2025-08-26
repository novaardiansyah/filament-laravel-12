<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailResource\Pages;
use App\Models\Email;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;

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
          Forms\Components\Group::make([
            Forms\Components\TextInput::make('to')
              ->label('Email Tujuan')
              ->required()
              ->email()
              ->maxLength(255)
              ->live(onBlur: true)
              ->afterStateUpdated(function (Forms\Set $set, string $state) {
                if (!$state) return;
                $name = textCapitalize(explode('@', $state)[0]);
                $set('recipient', $name);
              }),
            Forms\Components\TextInput::make('recipient')
              ->label('Kepada')
              ->required()
              ->maxLength(255),
          ])
          ->columns(2),

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
          ->searchable(),
        Tables\Columns\TextColumn::make('to')
          ->label('Email Tujuan')
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
      ->defaultSort('updated_at', 'desc')
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->color('info')
            ->icon('heroicon-o-eye')
            ->mutateRecordDataUsing(function (array $data, Email $record): array {
              $data['body'] = $record->email_log->message ?? $record->body;
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
                ->body('Pesan berhasil dikirim ke email: ' . $record->to)
                ->success()
                ->send();
            }),
          
          Tables\Actions\Action::make('preview_email')
            ->label('Pratinjau Email')
            ->icon('heroicon-o-document-magnifying-glass')
            ->color('warning')
            ->url(fn (Email $record): string => route('admin.emails.preview', $record))
            ->openUrlInNewTab(),

          Tables\Actions\Action::make('replicate')
            ->color('success')
            ->label('Duplikat')
            ->icon('heroicon-o-document-duplicate')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Duplikasi Email')
            ->action(function (Email $record, Tables\Actions\Action $action) {
              $newEmail = $record->replicate();

              $newEmail->code       = getCode('email');
              $newEmail->subject    = $record->subject . ' (copy)';
              $newEmail->has_send   = false;
              $newEmail->created_at = now();
              $newEmail->updated_at = now();

              $newEmail->save();
              $action->success();

              Notification::make()
                ->title('Duplikat Berhasil')
                ->body('Email berhasil diduplikat.')
                ->success()
                ->send();

              return redirect(url("/admin/emails?tableAction=edit&tableActionRecord={$newEmail->id}"));
            }),

          Tables\Actions\EditAction::make()
            ->color('primary')
            ->mutateRecordDataUsing(function (array $data): array {
              $data['save_as_draft'] = true;
              return $data;
            })
            ->after(function (array $data, Email $record) {
              $saveAsDraft = (bool) $data['save_as_draft'];
              if ($saveAsDraft) return;

              $record->sendEmail();
            }),

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

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Infolists\Components\Section::make([
            Infolists\Components\TextEntry::make('recipient')
              ->label('Kepada')
              ->formatStateUsing(fn(string $state): string => textLower($state)),
            Infolists\Components\TextEntry::make('subject')
              ->label('Subjek'),
            Infolists\Components\TextEntry::make('body')
              ->label('Pesan')
              ->columnSpanFull()
              ->html(),
          ])
          ->columns(2),

        Infolists\Components\Section::make([
            Infolists\Components\TextEntry::make('has_send')
              ->label('Status')
              ->formatStateUsing(fn(bool $state): string => $state ? 'Terkirim' : 'Belum Terkirim')
              ->badge()
              ->color(fn(bool $state): string => $state ? 'success' : 'danger'),
            Infolists\Components\TextEntry::make('created_at')
              ->label('Dibuat pada')
              ->dateTime('d M Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')
              ->label('Diubah pada')
              ->dateTime('d M Y H:i'),
          ])
          ->columns(3),
      ]);
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
