<?php

namespace App\Providers;

use App\Contracts\Repositories;
use App\Repositories\Post\Post as PostRepository;
use App\Repositories\User\User as UserRepository;
use App\Repositories\Comment\Comment as CommentRepository;
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
        $this->app->bind(Repositories\Post::class, PostRepository::class);
        $this->app->bind(Repositories\User::class, UserRepository::class);
        $this->app->bind(Repositories\Comment::class, CommentRepository::class);
    }
}
