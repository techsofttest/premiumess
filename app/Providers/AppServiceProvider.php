<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\View;
use App\Models\Category;
use App\Models\Product;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Payments\PaymentGatewayInterface::class,
            \App\Services\Payments\StripePaymentService::class
        );

        // Register import services as singletons so caches persist across rows
        $this->app->singleton(\App\Services\Import\BrandResolver::class);
        $this->app->singleton(\App\Services\Import\CategoryResolver::class);
        $this->app->singleton(\App\Services\Import\ProductResolver::class);
        $this->app->singleton(\App\Services\Import\VariantResolver::class);
        $this->app->singleton(\App\Services\Import\ImageResolver::class);
        $this->app->singleton(\App\Services\Import\ImportLogger::class);
        $this->app->singleton(\App\Services\Import\ProductImportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('filament-login', function (Request $request) {
            return Limit::perMinute(1000)->by(
                $request->ip() . '|' . $request->input('email')
            );
        });

        // Grant all permissions to Super Admin role
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

       View::composer(
    ['partials.header', 'partials.footer'],
    function ($view) {

        // Hierarchical categories: top-level with children
        $categories = Category::with(['children'])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->select('id', 'name', 'featured_image', 'slug', 'is_featured')
            ->get();

        $view->with([
            'full_categories' => $categories,
            'featured_products' => $featuredProducts,
        ]);
    }
);
    }
}
