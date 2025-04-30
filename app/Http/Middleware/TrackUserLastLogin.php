<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TrackUserLastLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::hasUser() && Auth::user()) {
            // Actualizar solo si ha pasado al menos 1 minuto desde la última actualización
            // para evitar actualizaciones constantes de la base de datos
            $user = Auth::user();

            if (
                !$user->last_login_at ||
                Carbon::parse($user->last_login_at)->diffInMinutes(now()) >= 1
            ) {

                $user->update([
                    'last_login_at' => now(),
                ]);
            }
        }

        return $next($request);
    }
}
