<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use App\Http\Middleware\SetSessionForAdmin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login()
            ->brandLogo(asset('images/logo/logo.webp?v1'))
            ->brandLogoHeight('3rem')
            ->middleware(['throttle:filament-login'])
            ->colors([
                'primary' => Color::hex('#0c9046'),
            ])  
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Delivery'),
                NavigationGroup::make()
                     ->label('Ecommerce'),
                NavigationGroup::make()
                    ->label('Content Management'),
                NavigationGroup::make()
                    ->label('Master Data'),
                NavigationGroup::make()
                    ->label('Staff Management'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // Widgets are automatically discovered
            ])
            ->assets([
                Css::make('delivery-map-css', resource_path('css/filament/delivery-map.css')),
                Css::make('delivery-staff-workflow-css', resource_path('css/filament/delivery-staff-workflow.css')),
                Css::make('app-css', resource_path('css/app.css')),
                Js::make('delivery-map-js', resource_path('js/filament/delivery-map.js'))->defer(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                SetSessionForAdmin::class,
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
            ->renderHook(
                \Filament\View\PanelsRenderHook::BODY_END,
                fn (): string => '<script>
                    document.addEventListener("livewire:init", () => {
                        if (typeof Livewire !== "undefined") {
                            Livewire.hook("request", ({ fail }) => {
                                fail(({ status, preventDefault }) => {
                                    if (status === 419) {
                                        preventDefault();
                                        window.location.reload();
                                    }
                                });
                            });
                        }
                    });
                </script>'
            );
    }
}
