<?php

namespace App\Providers\Filament;

use App\Colors\Color;
use App\Filament\Pages\Backups;
use App\Filament\Resources\MediaResource;
use App\Filament\Resources\PermissionResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\UserResource;
use App\Http\Middleware\SetLocaleFromQueryAndSession;
use Blade;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandLogo(fn () => view('components.app-logo-icon', [
                'attributes' => new ComponentAttributeBag([
                    'class' => 'size-9 fill-current text-black dark:text-white',
                ]),
            ]))
            ->colors([
                'primary' => Color::Vermilion,
                'secondary' => Color::WebOrange,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
                SetLocaleFromQueryAndSession::class,
            ])
            ->authMiddleware([
                EnsureEmailIsVerified::class,
                Authenticate::class,
            ])
            ->plugins([
                \Awcodes\Curator\CuratorPlugin::make(),
                \Awcodes\Overlook\OverlookPlugin::make()
                    ->columns([
                        'default' => 2,
                        'sm' => 3,
                        'md' => 4,
                    ])
                    ->includes([
                        MediaResource::class,
                        UserResource::class,
                        PermissionResource::class,
                        RoleResource::class,
                    ]),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin::make()
                    ->usingPage(Backups::class),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(fn () => __('navigation-menu.menu.home'))
                    ->icon('heroicon-o-home')
                    ->url(fn () => route('home')),
                MenuItem::make()
                    ->label(fn () => __('navigation-menu.menu.profile'))
                    ->icon('heroicon-o-user')
                    ->url(fn () => route('profile.show')),
                MenuItem::make()
                    ->label(fn () => __('navigation-menu.menu.api_tokens'))
                    ->icon('heroicon-o-key')
                    ->url(fn () => route('api-tokens.index')),
            ])
            ->renderHook(PanelsRenderHook::SCRIPTS_AFTER, fn () => Blade::render(<<<'BLADE'
            @vite('resources/ts/app.ts')
            BLADE))
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn () => Blade::render(<<<'BLADE'
            @googlefonts('sans')
            BLADE))
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => Blade::render('<x-navigation-menu.language-switcher />'))
            ->spa()
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('14rem')
            ->unsavedChangesAlerts()
            ->viteTheme('resources/css/app.css')
            ->widgets([
                \Awcodes\Overlook\Widgets\OverlookWidget::class,
            ]);
    }
}
