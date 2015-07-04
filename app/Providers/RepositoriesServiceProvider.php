<?php

namespace App\Providers;

use App\Contracts\Repository;
use App\Models\Post as PostModel;
use App\Repositories\Post\Cache;
use App\Repositories\Post\Post as PostRepository;
use Illuminate\Support\ServiceProvider;

/**
 * @author  Rohit Arora
 *
 * Class RepositoriesServiceProvider
 * @package App\Providers
 */
class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Repository::class, PostRepository::class);
    }
}
