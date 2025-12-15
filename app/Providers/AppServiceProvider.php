<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Pasien;
use App\Models\Dokter;
use App\Models\Konsultasi;
use App\Models\PesanChat;
use App\Models\User;
use App\Policies\PasienPolicy;
use App\Policies\DokterPolicy;
use App\Policies\KonsultasiPolicy;
use App\Policies\PesanChatPolicy;
use App\Policies\AdminPolicy;
use App\Services\AuthService;
use App\Services\ConsultationService;
use App\Services\PasienService;
use App\Services\DokterService;
use App\Services\PesanChatService;
use App\Services\RatingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register singleton services untuk dependency injection
        $this->app->singleton(AuthService::class, function () {
            return new AuthService();
        });

        $this->app->singleton(ConsultationService::class, function () {
            return new ConsultationService();
        });

        $this->app->singleton(PasienService::class, function () {
            return new PasienService();
        });

        $this->app->singleton(DokterService::class, function () {
            return new DokterService();
        });

        $this->app->singleton(PesanChatService::class, function () {
            return new PesanChatService();
        });

        $this->app->singleton(RatingService::class, function () {
            return new RatingService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Policies
        Gate::policy(Pasien::class, PasienPolicy::class);
        Gate::policy(Dokter::class, DokterPolicy::class);
        Gate::policy(Konsultasi::class, KonsultasiPolicy::class);
        Gate::policy(PesanChat::class, PesanChatPolicy::class);
        Gate::policy(User::class, AdminPolicy::class);

        // Define custom gates untuk role-based access
        Gate::define('is-admin', function (User $user) {
            return $user->role === 'admin';
        });

        Gate::define('is-dokter', function (User $user) {
            return $user->role === 'dokter';
        });

        Gate::define('is-pasien', function (User $user) {
            return $user->role === 'pasien';
        });
    }
}
