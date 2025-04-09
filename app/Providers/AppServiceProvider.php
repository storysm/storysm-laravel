<?php

namespace App\Providers;

use App\Colors\Color;
use App\Contracts\Jwt;
use App\Services\AhcJwtService;
use Exception;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use SolutionForest\FilamentTranslateField\Facades\FilamentTranslateField;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Jwt::class, function (Application $app) {
            return new AhcJwtService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'primary' => Color::Vermilion,
            'secondary' => Color::WebOrange,
        ]);
        FilamentView::spa();
        Table::configureUsing(function (Table $table): void {
            $table->paginationPageOptions([12, 24]);
        });

        Translatable::fallback(fallbackAny: true);

        /** @var string[]|null */
        $locales = config('app.supported_locales', null);
        if (! $locales) {
            throw new Exception('Supported locales are null.');
        }
        // @phpstan-ignore-next-line
        FilamentTranslateField::defaultLocales($locales);
    }
}
