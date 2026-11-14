<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceApplicationAccess
{
    private const MODULES = [
        'users' => 'users', 'roles' => 'users', 'auth/signup' => 'users', 'register' => 'users',
        'sales' => 'sales', 'customers' => 'customers',
        'purchases/suppliers' => 'suppliers', 'purchases/suppliers_profile' => 'suppliers',
        'purchases/credit_suplliers' => 'suppliers', 'purchases' => 'purchases',
        'products' => 'products', 'warehouses&branches' => 'warehouses', 'warehouses' => 'warehouses',
        'add_warehouses' => 'warehouses', 'update_warehouses' => 'warehouses', 'delete_warehouses' => 'warehouses',
        'expenses' => 'expenses', 'reports' => 'reports',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = trim($request->path(), '/');
        if ($path === '' || preg_match('#^(login|password/|email/verify)#', $path)) {
            return $next($request);
        }
        abort_if($path === 'register' || $path === 'auth/signup', 404);

        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (($user->status ?? 'active') !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'هذا الحساب غير نشط. تواصل مع مدير النظام.']);
        }

        $module = null;
        foreach (self::MODULES as $prefix => $mappedModule) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                $module = $mappedModule;
                break;
            }
        }

        if ($module) {
            $action = $this->actionFor($request, $path);
            abort_unless($user->can($module.'.'.$action), 403, 'ليس لديك صلاحية لتنفيذ هذا الإجراء.');
        }

        return $next($request);
    }

    private function actionFor(Request $request, string $path): string
    {
        $handler = (string) $request->route()?->getActionMethod();
        if (str_starts_with($handler, 'show') || in_array($handler, ['index', 'allInvoices', 'creditCustomers', 'creditSuppliers', 'searchProducts'], true)) {
            return 'view';
        }

        if ($request->isMethod('DELETE')) {
            return 'delete';
        }

        if ($request->isMethod('PUT') || $request->isMethod('PATCH')
            || preg_match('#(^|/)(update|edit|payment|transaction)(/|$)#i', $path)) {
            return 'update';
        }

        return $request->isMethod('POST') ? 'create' : 'view';
    }
}
