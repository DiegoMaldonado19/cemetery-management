<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $panel = null): Response
    {
        // Agregar logs para depuración
        Log::info('CheckUserRole middleware ejecutándose', [
            'url' => $request->url(),
            'panel' => $panel,
            'user' => Auth::hasUser() ? Auth::user()->email : 'no autenticado'
        ]);

        // Verificar que el usuario esté autenticado
        if (!Auth::hasUser() || !Auth::user()) {
            Log::info('Usuario no autenticado, redirigiendo a login');
            return redirect('/admin/login');
        }

        // Si no se especifica panel, permitir el acceso
        if ($panel === null) {
            Log::info('No se especificó panel, permitiendo acceso');
            return $next($request);
        }

        // Obtener el usuario y su rol
        $user = Auth::user();

        // Si el usuario no tiene un rol asignado, redirigir a login
        if (!$user->role) {
            Log::warning('Usuario sin rol asignado: ' . $user->email);
            Auth::logout();
            return redirect('/admin/login')->with('error', 'No tienes un rol asignado en el sistema.');
        }

        $roleName = $user->role->name;
        Log::info('Rol del usuario: ' . $roleName);

        // Administrador puede acceder a cualquier panel
        if ($roleName === 'Administrador') {
            Log::info('Usuario es Administrador, permitiendo acceso');
            return $next($request);
        }

        // Validar acceso según el panel y el rol
        $permitirAcceso = false;

        switch ($panel) {
            case 'admin':
                // Solo Administrador, Ayudante y Auditor pueden acceder al panel admin
                if (in_array($roleName, ['Administrador', 'Ayudante', 'Auditor'])) {
                    $permitirAcceso = true;
                }
                break;

            case 'consultation':
                // Solo Usuario de Consulta puede acceder al panel de consulta
                if ($roleName === 'Usuario de Consulta') {
                    $permitirAcceso = true;
                }
                break;
        }

        Log::info('Decisión de acceso para ' . $roleName . ' al panel ' . $panel . ': ' . ($permitirAcceso ? 'Permitido' : 'Denegado'));

        if ($permitirAcceso) {
            return $next($request);
        }

        // Determinar la redirección según el rol y panel
        if ($panel === 'consultation') {
            // Si es un usuario administrativo intentando acceder al panel de consulta, redirigir al admin
            if (in_array($roleName, ['Administrador', 'Ayudante', 'Auditor'])) {
                Log::info('Redirigiendo al admin desde panel de consulta');
                return redirect('/admin');
            }
        } else {
            // Si es un usuario de consulta intentando acceder al panel admin, redirigir a consulta
            if ($roleName === 'Usuario de Consulta') {
                Log::info('Redirigiendo a consulta desde panel admin');
                return redirect('/consulta');
            }
        }

        // Fallback por defecto - logout y mensaje de error
        Log::warning('Acceso denegado, haciendo logout: ' . $user->email);
        Auth::logout();
        return redirect('/admin/login')->with('error', 'No tienes permiso para acceder a esta sección.');
    }
}
