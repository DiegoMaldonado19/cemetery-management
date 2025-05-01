<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $panel = null): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::hasUser() || !Auth::user()) {
            return redirect('/admin/login');
        }

        // Si no se especifica panel, permitir el acceso
        if ($panel === null) {
            return $next($request);
        }

        // Obtener el usuario y su rol
        $user = Auth::user();

        // Si el usuario no tiene un rol asignado, redirigir a login
        if (!$user->role) {
            Auth::logout();
            return redirect('/admin/login')->with('error', 'No tienes un rol asignado en el sistema.');
        }

        $roleName = $user->role->name;

        // Administrador puede acceder a cualquier panel
        if ($roleName === 'Administrador') {
            return $next($request);
        }

        // Validar acceso según el panel y el rol
        switch ($panel) {
            case 'admin':
                // Solo Administrador, Ayudante y Auditor pueden acceder al panel admin
                if (in_array($roleName, ['Administrador', 'Ayudante', 'Auditor'])) {
                    return $next($request);
                }
                break;

            case 'consultation':
                // Solo Usuario de Consulta puede acceder al panel de consulta
                if ($roleName === 'Usuario de Consulta') {
                    return $next($request);
                }
                break;
        }

        // Si llegamos aquí, el usuario no tiene permiso para acceder al panel
        if ($panel === 'consultation') {
            // Si es un usuario administrativo intentando acceder al panel de consulta, redirigir al admin
            if (in_array($roleName, ['Administrador', 'Ayudante', 'Auditor'])) {
                return redirect('/admin');
            }
        } else {
            // Si es un usuario de consulta intentando acceder al panel admin, redirigir a consulta
            if ($roleName === 'Usuario de Consulta') {
                return redirect('/consulta');
            }
        }

        // Fallback por defecto - logout y mensaje de error
        Auth::logout();
        return redirect('/admin/login')->with('error', 'No tienes permiso para acceder a esta sección.');
    }
}
