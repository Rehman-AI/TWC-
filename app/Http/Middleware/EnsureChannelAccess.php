<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Channel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChannelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $channel = $request->route('channel');

        if ($channel instanceof Channel) {
            if (!$user->canAccessChannel($channel)) {
                abort(403, 'You do not have access to this channel.');
            }
        }

        return $next($request);
    }
}