<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\AccountStatus;
use App\Enums\AuditAction;
use App\Models\PlatformAccount;
use App\Services\AuditLogger;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RefreshOAuthTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct()
    {
        $this->queue = 'system';
    }

    public function handle(): void
    {
        $accounts = PlatformAccount::where('status', AccountStatus::Active)
            ->whereNotNull('refresh_token')
            ->where('token_expires_at', '<=', now()->addMinutes(30))
            ->get();

        foreach ($accounts as $account) {
            try {
                $this->refreshToken($account);
            } catch (\Throwable $e) {
                Log::error('Token refresh failed', [
                    'account'  => $account->id,
                    'platform' => $account->platform->value,
                    'error'    => $e->getMessage(),
                ]);

                $account->update(['status' => AccountStatus::Expired]);
            }
        }
    }

    private function refreshToken(PlatformAccount $account): void
    {
        $response = Http::post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $account->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Token refresh failed: ' . $response->body());
        }

        $tokens = $response->json();

        $account->update([
            'access_token'     => $tokens['access_token'],
            'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
        ]);

        app(AuditLogger::class)->log(
            AuditAction::TokenRefresh,
            $account,
            ['platform' => $account->platform->value],
        );

        Log::info('Token refreshed', ['account' => $account->id]);
    }
}