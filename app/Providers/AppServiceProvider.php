<?php

namespace App\Providers;

use App\Contracts\CaptionProviderInterface;
use App\Contracts\ScriptGeneratorInterface;
use App\Contracts\StockProviderInterface;
use App\Contracts\StorageProviderInterface;
use App\Contracts\TTSProviderInterface;
use App\Models\AuditLog;
use App\Models\Channel;
use App\Models\Team;
use App\Policies\AuditLogPolicy;
use App\Policies\ChannelPolicy;
use App\Policies\TeamPolicy;
use App\Services\AI\MockScriptGenerator;
use App\Services\AuditLogger;
use App\Services\Stock\MockStockProvider;
use App\Services\Storage\MockStorageProvider;
use App\Services\TTS\MockTTSProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class);

        // -- Pluggable Provider Bindings --
        // These use mock implementations for dev. Swap to real providers via config.

        $this->app->bind(ScriptGeneratorInterface::class, function () {
            // TODO: swap to ClaudeScriptGenerator when API key is configured
            return new MockScriptGenerator();
        });

        $this->app->bind(StorageProviderInterface::class, function () {
            // TODO: swap to GoogleDriveProvider when service account is configured
            return new MockStorageProvider();
        });

        $this->app->bind(TTSProviderInterface::class, function () {
            // TODO: swap to EdgeTTSProvider when edge-tts is installed
            return new MockTTSProvider();
        });

        $this->app->bind(StockProviderInterface::class, function () {
            // TODO: swap to PexelsProvider when PEXELS_API_KEY is set
            return new MockStockProvider();
        });
    }

    public function boot(): void
    {
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Channel::class, ChannelPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
    }
}