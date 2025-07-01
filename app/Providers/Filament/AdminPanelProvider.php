<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ActivityLogTable;
use App\Filament\Widgets\UserStatsOverview;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Filament\Forms\Components\FileUpload;

class AdminPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->default()
      ->id('admin')
      ->path('admin')
      ->login()
      ->registration()
      ->passwordReset()
      ->emailVerification()
      ->profile()
      ->favicon(asset('favicon.ico'))
      ->colors([
        'primary' => Color::Cyan,
      ])
      ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
      ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->databaseNotifications()
      ->sidebarCollapsibleOnDesktop()
      // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
      ->widgets([
        UserStatsOverview::class,
        ActivityLogTable::class,
      ])
      ->middleware([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        AuthenticateSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        SubstituteBindings::class,
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
      ])
      ->authMiddleware([
        Authenticate::class,
      ])
      ->resources([
        config('filament-logger.activity_resource')
      ])
      ->plugins([
        FilamentShieldPlugin::make(),
        BreezyCore::make()
          ->myProfile(
            shouldRegisterUserMenu: true,
            hasAvatars: true,
          )
          ->enableTwoFactorAuthentication()
          ->enableBrowserSessions()
          ->avatarUploadComponent(fn() => 
            FileUpload::make('avatar_url')
              ->directory('images/profile')
              ->image()
              ->imageEditor()
              ->enableOpen(),
          ),
          GlobalSearchModalPlugin::make()
      ])
    ->navigationItems([
      NavigationItem::make('Profil Saya')
        ->url(uri(url('admin/my-profile')))
        ->icon('heroicon-o-user')
        ->isActiveWhen(fn () => request()->is('admin/my-profile*'))
    ]);
  }
}
