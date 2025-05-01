<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log para depurar
        Log::info('RedirectBasedOnRole ejecutándose', [
            'url' => $request->url(),
            'path' => $request->path(),
            'user' => Auth::hasUser() ? Auth::user()->email : 'no autenticado'
        ]);

        // Si el usuario no está autenticado y está intentando acceder a la raíz,
        // redirigirlo al login de Filament
        if (!Auth::hasUser() && $request->is('/')) {
            Log::info('Redirigiendo usuario no autenticado a la página de login');
            return redirect('/admin/login');
        }

        // Si el usuario no está autenticado, permitir la solicitud
        // (importante para poder acceder a páginas de login)
        if (!Auth::hasUser() || !Auth::user()) {
            return $next($request);
        }

        $user = Auth::user();

        // Verificar si el usuario tiene un rol asignado
        if (!$user->role) {
            Log::warning('Usuario sin rol asignado: ' . $user->email);
            Auth::logout();
            return redirect('/admin/login')->with('error', 'No tienes un rol asignado en el sistema.');
        }

        $roleName = $user->role->name;
        Log::info('Rol del usuario: ' . $roleName);

        // Para solicitudes a la ruta raíz, redirigir según el rol
        if ($request->is('/')) {
            Log::info('Redirigiendo desde la raíz según el rol: ' . $roleName);
            if ($roleName === 'Usuario de Consulta') {
                return redirect('/consulta');
            } else {
                return redirect('/admin');
            }
        }

        // Verificar acceso basado en la URL actual
        $isAdminPath = $request->is('admin') || $request->is('admin/*');
        $isConsultationPath = $request->is('consulta') || $request->is('consulta/*');

        // Si es Usuario de Consulta intentando acceder al panel de administración
        if ($roleName === 'Usuario de Consulta' && $isAdminPath) {
            Log::info('Usuario de Consulta intentando acceder al panel admin, redirigiendo');
            return redirect('/consulta')
                ->with('error', 'No tienes permiso para acceder al panel de administración.');
        }

        // Si es usuario administrativo intentando acceder al panel de consulta
        if (in_array($roleName, ['Administrador', 'Ayudante', 'Auditor']) && $isConsultationPath) {
            Log::info('Usuario administrativo intentando acceder al panel de consulta, redirigiendo');
            return redirect('/admin')
                ->with('error', 'Como administrador, debes utilizar el panel administrativo.');
        }

        return $next($request);
    }
}
