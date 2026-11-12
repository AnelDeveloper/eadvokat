<?php

namespace App\Providers;

use App\Contracts\DocumentExtractor;
use App\Contracts\SpeechTranscriber;
use App\Services\Extraction\FakeDocumentExtractor;
use App\Services\Speech\FakeSpeechTranscriber;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
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
