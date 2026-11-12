<?php

namespace App\Providers;

use App\Contracts\DocumentExtractor;
use App\Contracts\SpeechTranscriber;
use App\Services\Extraction\FakeDocumentExtractor;
use App\Services\Speech\FakeSpeechTranscriber;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only fake drivers exist (rule 8): no external AI, OCR or speech service.
        $this->app->bind(DocumentExtractor::class, FakeDocumentExtractor::class);
        $this->app->bind(SpeechTranscriber::class, FakeSpeechTranscriber::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Each new demo office is a database write; cap it per visitor.
        RateLimiter::for('login-api', fn (Request $request) => Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()));
        RateLimiter::for('demo', fn (Request $request) => Limit::perHour(30)->by($request->ip()));
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
