<?php

namespace App\Providers;

use App\Models\JobSeeker;
use App\Models\Configuration;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
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
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // Get the first configuration record
        $configuration = Configuration::first();

        // Share the configuration if it exists
        view()->share('configuration', $configuration);

        View::composer('*', function ($view) {
            $jobseekerExists = false;
            if (Auth::check()) {
                $user = Auth::user();
                $jobseekerExists = JobSeeker::where('id', $user->id)->exists();
            }

            // Log to check the value of jobseekerExists
            \Log::info('jobseekerExists: ' . ($jobseekerExists ? 'true' : 'false'));

            $view->with('jobseekerExists', $jobseekerExists);
        });
    }
}