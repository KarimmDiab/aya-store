<?php

namespace App\Http\Controllers;

use App\Models\AccessRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class rolesController extends Controller
{
    private const MODULES = [
        'dashboard' => 'الرئيسية', 'sales' => 'المبيعات', 'customers' => 'العملاء',
        'purchases' => 'المشتريات', 'suppliers' => 'الموردين', 'products' => 'المنتجات',
        'warehouses' => 'المخازن والفروع', 'expenses' => 'المصروفات', 'reports' => 'التقارير',
        'users' => 'المستخدمين والصلاحيات',
    ];

    private const ACTIONS = ['view' => 'عرض', 'create' => 'إضافة', 'update' => 'تعديل', 'delete' => 'حذف'];

    public function index()
    {
        $roles = AccessRole::query()->with('permissions')->withCount('users')->orderBy('id')->get();
        $permissions = Permission::query()->where('guard_name', 'web')->orderBy('name')->get();
        $modules = self::MODULES;
        $actions = self::ACTIONS;

        return view('users.roles', compact('roles', 'permissions', 'modules', 'actions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'add_role' => ['required', 'string', 'max:100', 'unique:access_roles,name'],
            'role_desc' => ['nullable', 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);
        $this->ensurePermissionsAreAssignable($data['permissions'] ?? []);

        DB::transaction(function () use ($data) {
            $role = AccessRole::create(['name' => $data['add_role'], 'description' => $data['role_desc'] ?? null, 'guard_name' => 'web']);
            $role->syncPermissions($data['permissions'] ?? []);
        });

        return redirect()->route('all_roles')->with('success', 'تم إنشاء المجموعة وحفظ صلاحياتها.');
    }

    public function update(Request $request, int $id)
    {
        $role = AccessRole::findOrFail($id);
        $nameRules = ['required', 'string', 'max:100', Rule::unique('access_roles', 'name')->ignore($role->id)];
        if (in_array($role->name, ['مدير النظام', 'Super admin'], true)) {
            $nameRules[] = Rule::in([$role->name]);
        }
        $data = $request->validate([
            'add_role' => $nameRules,
            'role_desc' => ['nullable', 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ]);
        if (! in_array($role->name, ['مدير النظام', 'Super admin'], true)) {
            $this->ensurePermissionsAreAssignable($data['permissions'] ?? []);
        }

        DB::transaction(function () use ($role, $data) {
            $role->update(['name' => $data['add_role'], 'description' => $data['role_desc'] ?? null]);
            if (! in_array($role->name, ['مدير النظام', 'Super admin'], true)) {
                $role->syncPermissions($data['permissions'] ?? []);
            }
        });

        return redirect()->route('all_roles')->with('success', 'تم تحديث المجموعة وصلاحياتها.');
    }

    public function destroy(int $id)
    {
        $role = AccessRole::findOrFail($id);
        abort_if(in_array($role->name, ['مدير النظام', 'Super admin'], true), 422, 'لا يمكن حذف دور مدير النظام.');
        abort_if($role->users()->exists(), 422, 'انقل المستخدمين إلى مجموعة أخرى قبل حذف هذه المجموعة.');
        $role->delete();

        return redirect()->route('all_roles')->with('success', 'تم حذف المجموعة الإدارية.');
    }

    private function ensurePermissionsAreAssignable(array $permissions): void
    {
        $actor = auth()->user();
        if ($actor->hasAnyRole(['مدير النظام', 'Super admin'])) {
            return;
        }

        abort_unless(collect($permissions)->diff($actor->getAllPermissions()->pluck('name'))->isEmpty(), 403,
            'لا يمكنك منح صلاحيات لا تملكها.');
    }
}
