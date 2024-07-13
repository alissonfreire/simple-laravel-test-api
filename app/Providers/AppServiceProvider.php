<?php

namespace App\Providers;

use App\Interfaces\TodoRepositoryInterface;
use App\Repositories\TodoRepository;
use App\Services\TodoService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TodoRepositoryInterface::class, TodoRepository::class);
        $this->app->bind(TodoService::class, function ($app) {
            return new TodoService($app->make(TodoRepositoryInterface::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
