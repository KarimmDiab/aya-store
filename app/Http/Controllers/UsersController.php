<?php

namespace App\Http\Controllers;

use App\Models\AccessRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::query()->whereNull('deleted_at')->with('roles')->orderBy('id')->get();
        $roles = AccessRole::query()->orderBy('name')->get();

        return view('users.all_users', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'role' => ['required', 'exists:access_roles,id'],
            'status' => ['required', 'in:active,deactive'],
        ]);
        $role = AccessRole::findById($data['role'], 'web');
        $this->ensureRoleIsAssignable($role);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['user_name'], 'email' => $data['email'], 'password' => $data['password'],
                'mobile_number' => $data['user_phone'] ?? null, 'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'], 'role' => $data['role'], 'status' => $data['status'],
            ]);
            $user->assignRole(AccessRole::findById($data['role'], 'web'));
        });

        return redirect()->route('all_users')->with('success', 'تم تسجيل المستخدم بنجاح');
    }

    public function update(Request $request, int $id)
    {
        $user = User::query()->whereNull('deleted_at')->findOrFail($id);
        $data = $request->validate([
            'user_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female'],
            'role' => ['required', 'exists:access_roles,id'],
            'status' => ['required', 'in:active,deactive'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        $role = AccessRole::findById($data['role'], 'web');
        $this->ensureRoleIsAssignable($role);

        abort_if($user->is(auth()->user()) && $data['status'] !== 'active', 422, 'لا يمكن تعطيل حسابك أثناء تسجيل الدخول.');
        abort_if($user->is(auth()->user()) && (int) $user->role !== (int) $data['role'], 422, 'لا يمكن تغيير مجموعتك الإدارية من حسابك الحالي.');

        DB::transaction(function () use ($user, $data) {
            $attributes = [
                'name' => $data['user_name'], 'email' => $data['email'],
                'mobile_number' => $data['user_phone'] ?? null, 'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'], 'role' => $data['role'], 'status' => $data['status'],
            ];
            if (! empty($data['password'])) $attributes['password'] = $data['password'];
            $user->update($attributes);
            $user->syncRoles([AccessRole::findById($data['role'], 'web')]);
        });

        return redirect()->route('all_users')->with('success', 'تم تعديل بيانات المستخدم بنجاح');
    }

    public function destroy(int $id)
    {
        abort_if((int) auth()->id() === $id, 422, 'لا يمكن حذف حسابك الشخصي.');

        $deleted = User::query()->whereNull('deleted_at')->whereKey($id)->update(['deleted_at' => now()]);

        return redirect()->route('all_users')->with($deleted ? 'success' : 'error', $deleted ? 'تم حذف المستخدم بنجاح.' : 'هذا المستخدم غير موجود.');
    }

    private function ensureRoleIsAssignable(AccessRole $role): void
    {
        $actor = auth()->user();
        if ($actor->hasAnyRole(['مدير النظام', 'Super admin'])) {
            return;
        }

        $actorPermissions = $actor->getAllPermissions()->pluck('name');
        $requestedPermissions = $role->permissions()->pluck('permissions.name');
        abort_unless($requestedPermissions->diff($actorPermissions)->isEmpty(), 403, 'لا يمكنك إسناد مجموعة بصلاحيات أعلى من صلاحياتك.');
        abort_unless(! in_array($role->name, ['مدير النظام', 'Super admin'], true), 403, 'لا يمكنك إسناد دور مدير النظام.');
    }
}
