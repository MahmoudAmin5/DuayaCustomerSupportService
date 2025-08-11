<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\ChatServiceInterface;
use App\Services\ChatService;
use App\Services\Interfaces\AgentServiceInterface;
use App\Services\AgentService;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use App\Repositories\MessageRepository;
use App\Repositories\Interfaces\ChatRepositoryInterface;
use App\Repositories\ChatRepository;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\AgentRepositoryInterface;
use App\Repositories\AgentRepository;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ChatRepositoryInterface::class, ChatRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ChatServiceInterface::class, ChatService::class);
        $this->app->bind(AgentRepositoryInterface::class, AgentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
