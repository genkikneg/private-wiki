<?php

namespace App\Providers;

use App\Models\FavoriteTag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            if (! Auth::check()) {
                $view->with('favoriteTags', collect());
                return;
            }

            $view->with(
                'favoriteTags',
                FavoriteTag::with('tag')->ordered()->get()
            );
        });
    }
}
