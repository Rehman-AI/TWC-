<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        if (!$user->team_id || !$user->currentTeam) {
            abort(403, 'You must belong to a team to access this resource.');
        }

        if (!$user->currentTeam->hasMember($user)) {
            abort(403, 'You do not have access to this team.');
        }

        return $next($request);
    }
}