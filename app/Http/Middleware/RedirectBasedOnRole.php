<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si el usuario no está autenticado, permitir la solicitud
        // (esto es importante para poder acceder a la página de login)
        if (!Auth::hasUser() || !Auth::user()) {
            return $next($request);
        }

        $user = Auth::user();

        // Verificar si el usuario tiene un rol asignado
        if (!$user->role) {
            Auth::logout();
            return redirect('/admin/login')->with('error', 'No tienes un rol asignado en el sistema.');
        }

        $roleName = $user->role->name;

        // Verificar acceso basado en la URL actual
        $isAdminPath = $request->is('admin') || $request->is('admin/*');
        $isConsultationPath = $request->is('consulta') || $request->is('consulta/*');

        // Si es Usuario de Consulta intentando acceder al panel de administración
        if ($roleName === 'Usuario de Consulta' && $isAdminPath) {
            return redirect('/consulta')
                ->with('error', 'No tienes permiso para acceder al panel de administración.');
        }

        // Si es usuario administrativo intentando acceder al panel de consulta
        if (in_array($roleName, ['Administrador', 'Ayudante', 'Auditor']) && $isConsultationPath) {
            return redirect('/admin')
                ->with('error', 'Como administrador, debes utilizar el panel administrativo.');
        }

        return $next($request);
    }
}
