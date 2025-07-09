<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ActivityLogTable;
use App\Filament\Widgets\PaymentStatsOverview;
use App\Filament\Widgets\UserStatsOverview;
use App\Models\Setting;
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
      ->brandName(fn() => Setting::where('key', 'site_name')->first()?->value ?? 'Aplikasi')
      ->id('admin')
      ->path('admin')
      ->login()
      ->when(
        Setting::where('key', 'site_registration')->first()?->value === 'Ya',
        fn($panel) => $panel->registration()
      )
      ->when(
        Setting::where('key', 'site_password_reset')->first()?->value === 'Ya',
        fn($panel) => $panel->passwordReset()
      )
      ->emailVerification()
      ->profile()
      ->favicon(asset('favicon.ico'))
      ->colors(function () {
        $color = Setting::where('key', 'site_theme')->first()?->value ?? 'Cyan';
        return [
          'primary' => Color::{$color}
        ];
      })
      ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
      ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->databaseNotifications()
      ->sidebarCollapsibleOnDesktop()
      // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
      ->widgets([
        PaymentStatsOverview::class,
        ActivityLogTable::class,
        UserStatsOverview::class,
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
        FilamentShieldPlugin::make()
          ->gridColumns([
            'default' => 1,
            'sm'      => 2,
            'lg'      => 2
          ])
          ->sectionColumnSpan(1)
          ->checkboxListColumns([
            'default' => 1,
            'sm'      => 2,
            'lg'      => 4,
          ])
          ->resourceCheckboxListColumns([
            'default' => 1,
            'sm'      => 2,
          ]),

        BreezyCore::make()
          ->myProfile(
            shouldRegisterUserMenu: true,
            hasAvatars: true,
          )
          ->enableTwoFactorAuthentication()
          ->enableBrowserSessions()
          ->avatarUploadComponent(
            fn() =>
            FileUpload::make('avatar_url')
              ->directory('images/profile')
              ->image()
              ->imageEditor()
              ->enableOpen()
              ->enableDownload(),
          ),
        GlobalSearchModalPlugin::make()
      ])
      ->navigationGroups([
        'Pengguna',
        'Keuangan',
        'Master Data',
        'Pengaturan',
      ])
      ->navigationItems([
        NavigationItem::make('Profil Saya')
          ->group('Pengguna')
          ->url(uri(url('admin/my-profile')))
          ->icon('heroicon-o-user')
          ->isActiveWhen(fn() => request()->is('admin/my-profile*'))
      ]);
  }
}
