<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Une API ne redirige jamais vers une page de login (qui n'existe pas) :
        // toute requete /api ou attendant du JSON obtient un 401 propre plutot
        // qu un 500 "Route [login] not defined".
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }
        return null;
    }
}
