<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Enums\AuditAction;
use App\Enums\PlatformEnum;
use App\Models\Channel;
use App\Models\PlatformAccount;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class OAuthController extends Controller
{
    private const YOUTUBE_AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const YOUTUBE_TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const YOUTUBE_SCOPES = [
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/youtube.readonly',
    ];

    /**
     * Redirect user to YouTube OAuth consent screen.
     */
    public function youtubeRedirect(Request $request): RedirectResponse
    {
        $channelId = $request->query('channel');

        if (! $channelId) {
            return redirect()->route('filament.admin.resources.channels.index')
                ->with('error', 'Channel ID is required.');
        }

        $channel = Channel::findOrFail($channelId);

        // Store channel ID in session for callback
        session(['oauth_channel_id' => $channel->id]);

        $params = http_build_query([
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => config('services.google.redirect_uri'),
            'response_type' => 'code',
            'scope'         => implode(' ', self::YOUTUBE_SCOPES),
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => csrf_token(),
        ]);

        return redirect(self::YOUTUBE_AUTH_URL . '?' . $params);
    }

    /**
     * Handle YouTube OAuth callback.
     */
    public function youtubeCallback(Request $request): RedirectResponse
    {
        $channelId = session('oauth_channel_id');

        if (! $channelId) {
            return redirect()->route('filament.admin.resources.channels.index')
                ->with('error', 'OAuth session expired. Please try again.');
        }

        if ($request->has('error')) {
            Log::warning('YouTube OAuth denied', ['error' => $request->get('error')]);
            return redirect()->route('filament.admin.resources.channels.edit', $channelId)
                ->with('error', 'YouTube authorization was denied.');
        }

        $code = $request->get('code');

        try {
            // Exchange code for tokens
            $response = \Illuminate\Support\Facades\Http::post(self::YOUTUBE_TOKEN_URL, [
                'code'          => $code,
                'client_id'     => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri'  => config('services.google.redirect_uri'),
                'grant_type'    => 'authorization_code',
            ]);

            if (! $response->successful()) {
                throw new \RuntimeException('Token exchange failed: ' . $response->body());
            }

            $tokens = $response->json();

            // Get YouTube channel info
            $channelInfo = \Illuminate\Support\Facades\Http::withToken($tokens['access_token'])
                ->get('https://www.googleapis.com/youtube/v3/channels', [
                    'part' => 'snippet',
                    'mine' => 'true',
                ]);

            $ytChannel = $channelInfo->json()['items'][0] ?? null;

            // Create or update platform account
            $account = PlatformAccount::updateOrCreate(
                [
                    'channel_id' => $channelId,
                    'platform'   => PlatformEnum::YouTube,
                ],
                [
                    'platform_account_id' => $ytChannel['id'] ?? null,
                    'account_name'        => $ytChannel['snippet']['title'] ?? 'YouTube Channel',
                    'access_token'        => $tokens['access_token'],
                    'refresh_token'       => $tokens['refresh_token'] ?? null,
                    'token_expires_at'    => now()->addSeconds($tokens['expires_in'] ?? 3600),
                    'scopes'              => self::YOUTUBE_SCOPES,
                    'status'              => AccountStatus::Active,
                    'metadata'            => [
                        'youtube_channel_id' => $ytChannel['id'] ?? null,
                        'thumbnail'          => $ytChannel['snippet']['thumbnails']['default']['url'] ?? null,
                    ],
                ]
            );

            // Audit log
            app(AuditLogger::class)->logCreated(
                AuditAction::ChannelAccessGranted,
                $account,
                ['platform' => 'youtube']
            );

            session()->forget('oauth_channel_id');

            return redirect()->route('filament.admin.resources.channels.edit', $channelId)
                ->with('success', 'YouTube account connected successfully!');

        } catch (\Throwable $e) {
            Log::error('YouTube OAuth callback failed', [
                'error'   => $e->getMessage(),
                'channel' => $channelId,
            ]);

            return redirect()->route('filament.admin.resources.channels.edit', $channelId)
                ->with('error', 'Failed to connect YouTube: ' . $e->getMessage());
        }
    }
}