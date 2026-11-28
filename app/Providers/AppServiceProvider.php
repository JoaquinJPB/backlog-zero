<?php

namespace App\Providers;

use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureRouteBindings();
    }

    /**
     * {user} in a URL is always a username. Explicit rather than implicit
     * binding because the profile sections are also served on routes whose
     * controller doesn't take the model (/journal, /library), and implicit
     * binding only substitutes what the signature asks for.
     */
    protected function configureRouteBindings(): void
    {
        Route::bind('user', fn (string $value): User => User::query()
            ->where('username', $value)
            ->firstOrFail());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
