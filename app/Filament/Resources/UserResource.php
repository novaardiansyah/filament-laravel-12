<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
  protected static ?string $model = User::class;

  protected static ?string $navigationIcon = 'heroicon-o-user-group';
  protected static ?string $navigationGroup = 'Hak Akses';
  protected static ?int $navigationSort = 10;

  protected static ?string $recordTitleAttribute = 'name';

  public static function getGloballySearchableAttributes(): array
  {
    return ['name', 'email'];
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->description('Informasi akun pengguna')
          ->collapsible()
          ->columns(2)
          ->columnSpan(2)
          ->schema([
            Forms\Components\TextInput::make('name')
              ->label('Nama lengkap')
              ->required()
              ->maxLength(255),

            Forms\Components\TextInput::make('email')
              ->label('Email')
              ->email()
              ->required()
              ->maxLength(255)
              ->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('password')
              ->label('Kata sandi')
              ->password()
              ->revealable()
              ->minLength(8)
              ->required(fn (Forms\Get $get) => $get('id') === null),

            Forms\Components\DateTimePicker::make('email_verified_at')
              ->label('Verifikasi pada')
              ->native(false)
              ->disabledOn('create')
              ->displayFormat('d/m/Y H:i'),

            Forms\Components\FileUpload::make('avatar_url')
              ->label('Foto Profil')
              ->disk('public')
              ->directory('images/profile')
              ->image()
              ->imageEditor(),
          ]),

        Forms\Components\Section::make()
          ->description('Hak akses pengguna')
          ->collapsible()
          ->columnSpan(1)
          ->schema([
            Forms\Components\CheckboxList::make('roles')
              ->relationship('roles', 'name')
              ->searchable(),
          ])
      ])
      ->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\ImageColumn::make('avatar_url')
          ->label('Foto Profil')
          ->circular()
          ->size(40)
          ->toggleable(),
        Tables\Columns\TextColumn::make('name')
          ->label('Nama Lengkap')
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('roles.name')
          ->label('Role')
          ->formatStateUsing(fn ($state) => collect((array) $state)
            ->map(fn ($role) => ucwords(str_replace('_', ' ', $role)))
            ->implode(', ')
          ),
        Tables\Columns\TextColumn::make('email')
          ->label('Email')
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('email_verified_at')
          ->label('Verifikasi pada')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(),
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
        Tables\Filters\Filter::make('date')
          ->form([
            Forms\Components\DatePicker::make('from_created_at')
              ->label('Dari Tanggal')
              ->displayFormat('d M Y')
              ->native(false),
            Forms\Components\DatePicker::make('end_created_at')
              ->label('Sampai Tanggal')
              ->displayFormat('d M Y')
              ->native(false),
          ])
          ->indicateUsing(function (array $data): ?array {
            $indicators = [];

            if ($data['from_created_at'] ?? null) {
              $indicators[] = Indicator::make('Dari Tanggal ' . Carbon::parse($data['from_created_at'])->translatedFormat('d M Y'))
                ->removeField('from_created_at');
            }

            if ($data['end_created_at'] ?? null) {
              $indicators[] = Indicator::make('Sampai Tanggal ' . Carbon::parse($data['end_created_at'])->translatedFormat('d M Y'))
                ->removeField('end_created_at');
            }

            return $indicators;
          })
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['from_created_at'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
              )
              ->when(
                $data['end_created_at'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
              );
          })
          ->columns(2)
      ], layout: FiltersLayout::Modal)
      ->filtersFormColumns(2)
      ->filtersFormSchema(fn (array $filters): array => [
        Forms\Components\Section::make('')
          ->description('Filter data berdasarkan kriteria berikut:')
          ->schema([
            $filters['date']
          ])
          ->columns(1)
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary'),

          Tables\Actions\DeleteAction::make(),
          Tables\Actions\ForceDeleteAction::make(),
          Tables\Actions\RestoreAction::make(),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\ForceDeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make(),
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
      'index'  => Pages\ListUsers::route('/'),
      'create' => Pages\CreateUser::route('/create'),
      'edit'   => Pages\EditUser::route('/{record}/edit'),
    ];
  }
}
