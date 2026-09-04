<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get the admin email from config
        $adminEmail = config('services.filament.admin_email');
        
        // If no admin email is configured, block access
        if (empty($adminEmail)) {
            abort(403, 'Platform owner not configured.');
        }

        // Allow only the user with the admin email
        if (! $request->user() || $request->user()->email !== $adminEmail) {
            abort(403, 'Access denied. Platform owner only.');
        }

        return $next($request);
    }
}
