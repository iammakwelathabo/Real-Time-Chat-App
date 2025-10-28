<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Events\UserStatusUpdated;

class UpdateOnlineStatus
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            Auth::user()->update([
                'is_online' => true,
                'last_status_update' => now(),
            ]);

            // Broadcast to presence channel
            event(new UserStatusUpdated(Auth::user(), true));
        }
        return $next($request);
    }
}
