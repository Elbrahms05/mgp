<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceVbgAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->can('vbg.view_decrypted')) {
            abort(403, 'Accès aux données VBG refusé. Permission insuffisante.');
        }

        return $next($request);
    }
}
