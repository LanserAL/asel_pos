<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSuspended
{
    /**
     * Verifica si el tenant del usuario autenticado está suspendido.
     * Si lo está, solo permite acceso al dashboard (donde verá el aviso)
     * y bloquea el acceso a todos los demás módulos.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado o no tiene tenant (SuperAdmin), dejar pasar
        if (!$user || !$user->tenant_id) {
            return $next($request);
        }

        $tenant = $user->tenant;

        // Si el tenant no existe o está activo, dejar pasar
        if (!$tenant || $tenant->status === 'active') {
            return $next($request);
        }

        // Tenant suspendido: solo permitir acceso al dashboard y logout
        $allowedRoutes = [
            'admin.dashboard',
            'vendedor.dashboard',
            'admin.settings',
            'logout',
        ];

        $currentRoute = $request->route()?->getName();

        if ($currentRoute && !in_array($currentRoute, $allowedRoutes)) {
            // Redirigir al dashboard correspondiente con mensaje de suspensión
            $dashboardRoute = $user->hasRole('admin') ? 'admin.dashboard' : 'vendedor.dashboard';
            
            return redirect()
                ->route($dashboardRoute)
                ->with('tenant_suspended', true);
        }

        // Incluso en rutas permitidas, marcar la sesión para mostrar el toast
        session()->flash('tenant_suspended', true);

        return $next($request);
    }
}
