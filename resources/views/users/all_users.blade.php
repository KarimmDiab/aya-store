@extends('layouts.master')

<title>معرض أيه - المستخدمون</title>

@section('css')
<style>
    .user-management { --um-primary:#3157d5; --um-ink:#17233c; --um-muted:#718096; }
    .user-management .um-hero { padding:26px 28px; border:0; border-radius:18px; color:#fff; background:linear-gradient(115deg,#263c91,#4268e8); box-shadow:0 12px 28px rgba(49,87,213,.16); }
    .user-management .um-hero h2 { color:#fff; font-weight:800; margin:0 0 7px; }
    .user-management .um-hero p { color:rgba(255,255,255,.78); margin:0; }
    .user-management .um-stat { border:1px solid #edf0f7; border-radius:15px; box-shadow:0 5px 18px rgba(25,42,70,.04); }
    .user-management .um-stat-label { color:var(--um-muted); font-size:13px; }
    .user-management .um-stat-value { color:var(--um-ink); font-size:25px; font-weight:800; }
    .user-management .um-card { border:1px solid #edf0f7; border-radius:16px; box-shadow:0 8px 26px rgba(25,42,70,.05); overflow:hidden; }
    .user-management .um-card .card-header { background:#fff; border-bottom:1px solid #edf0f7; padding:19px 22px; }
    .user-management .um-table th { color:#77839b; font-size:12px; font-weight:700; border-top:0; white-space:nowrap; }
    .user-management .um-table td { vertical-align:middle; color:#34415b; }
    .user-management .um-avatar { display:inline-flex; width:38px; height:38px; align-items:center; justify-content:center; border-radius:12px; background:#edf1ff; color:#3157d5; font-weight:800; margin-inline-end:9px; }
    .user-management .um-status { border-radius:30px; padding:6px 11px; font-size:11px; font-weight:700; }
    .user-management .um-status.active { color:#13835d; background:#e7f7f0; }
    .user-management .um-status.deactive { color:#bd4c56; background:#fff0f0; }
    .user-management .um-search { max-width:270px; border-radius:9px; }
    .user-management .form-control { border-radius:9px; min-height:41px; }
    .user-management .modal-content { border:0; border-radius:16px; overflow:hidden; }
    .user-management .modal-header { background:#f7f8fc; }
    .user-management .modal-title { font-weight:800; color:#17233c; }
    .user-management .field-label { font-size:13px; font-weight:700; color:#3e4b65; margin-bottom:6px; }
    .user-management .um-tabs { display:flex; gap:8px; margin:16px 0; }
    .user-management .um-tabs a { padding:9px 15px; border-radius:9px; color:#60708c; background:#fff; border:1px solid #e9edf5; font-weight:700; }
    .user-management .um-tabs a.active { color:#3157d5; background:#edf1ff; border-color:#dbe3ff; }
    @media(max-width:767px){.user-management .um-hero{padding:21px}.user-management .um-card .card-header{align-items:flex-start!important;gap:12px;flex-direction:column}.user-management .um-search{max-width:100%;width:100%}}
</style>
@endsection

@section('page-header')
<div class="breadcrumb-header justify-content-between"><div class="my-auto"><h4 class="content-title mb-0">إدارة المستخدمين والصلاحيات</h4></div></div>
@endsection

@section('content')
<div class="user-management">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="card um-hero mb-4"><div class="d-flex flex-wrap align-items-center justify-content-between">
        <div><h2>مساحة إدارة الفريق</h2><p>أنشئ حسابات المستخدمين، ووزّعهم على مجموعات بصلاحيات واضحة.</p></div>
        @can('users.create')<button class="btn btn-light mt-3 mt-md-0" type="button" data-toggle="modal" data-target="#addUser"><i class="fa fa-user-plus ml-1"></i> إضافة مستخدم</button>@endcan
    </div></div>

    <div class="um-tabs"><a class="active" href="{{ route('all_users') }}">المستخدمون</a><a href="{{ route('all_roles') }}">المجموعات والصلاحيات</a></div>

    <div class="row mb-3">
        <div class="col-md-4 mb-3"><div class="card um-stat"><div class="card-body"><div class="um-stat-label">إجمالي المستخدمين</div><div class="um-stat-value">{{ $users->count() }}</div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card um-stat"><div class="card-body"><div class="um-stat-label">حسابات نشطة</div><div class="um-stat-value">{{ $users->where('status', 'active')->count() }}</div></div></div></div>
        <div class="col-md-4 mb-3"><div class="card um-stat"><div class="card-body"><div class="um-stat-label">المجموعات الإدارية</div><div class="um-stat-value">{{ $roles->count() }}</div></div></div></div>
    </div>

    <div class="card um-card">
        <div class="card-header d-flex justify-content-between align-items-center"><div><h5 class="mb-1">حسابات المستخدمين</h5><small class="text-muted">إدارة بيانات الدخول والحالة والمجموعة الإدارية.</small></div><input id="userSearch" class="form-control um-search" type="search" placeholder="ابحث بالاسم أو البريد أو المجموعة"></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table um-table mb-0" id="usersTable">
            <thead><tr><th class="pr-4">المستخدم</th><th>رقم الهاتف</th><th>المجموعة</th><th>الحالة</th><th>تاريخ التحديث</th><th class="text-center">الإجراءات</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                @php($groupName = optional($roles->firstWhere('id', $user->role))->name ?? optional($user->roles->first())->name ?? 'بدون مجموعة')
                <tr class="user-row"><td class="pr-4"><span class="um-avatar">{{ mb_substr($user->name, 0, 1) }}</span><span class="font-weight-bold">{{ $user->name }}</span><div class="small text-muted mr-5">{{ $user->email }}</div></td>
                    <td>{{ $user->mobile_number ?: '—' }}</td><td><span class="badge badge-light px-2 py-2">{{ $groupName }}</span></td>
                    <td><span class="um-status {{ $user->status === 'active' ? 'active' : 'deactive' }}">{{ $user->status === 'active' ? 'نشط' : 'غير نشط' }}</span></td>
                    <td>{{ optional($user->updated_at)->format('Y-m-d') ?: '—' }}</td>
                    <td class="text-center text-nowrap">@can('users.update')<button class="btn btn-sm btn-outline-primary" type="button" data-toggle="modal" data-target="#editUser{{ $user->id }}">تعديل</button>@endcan
                        @can('users.delete') @if(auth()->id() !== $user->id)<button class="btn btn-sm btn-outline-danger" type="button" data-toggle="modal" data-target="#deleteUser{{ $user->id }}">حذف</button>@endif @endcan</td></tr>
            @empty<tr><td colspan="6" class="text-center text-muted py-5">لا يوجد مستخدمون بعد. أضف أول حساب من الزر أعلاه.</td></tr>@endforelse
            </tbody>
        </table></div></div>
    </div>

    @canany(['users.update', 'users.delete'])
    @foreach($users as $user)
        @can('users.update')
        <div class="modal fade" id="editUser{{ $user->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('updateUser', $user->id) }}">@csrf @method('PUT')
            <div class="modal-header"><h5 class="modal-title">تعديل حساب {{ $user->name }}</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body"><div class="row">
                <div class="col-md-6 form-group"><label class="field-label">الاسم الكامل</label><input class="form-control" name="user_name" value="{{ $user->name }}" required maxlength="255"></div>
                <div class="col-md-6 form-group"><label class="field-label">البريد الإلكتروني</label><input class="form-control" type="email" name="email" value="{{ $user->email }}" required></div>
                <div class="col-md-6 form-group"><label class="field-label">رقم الهاتف</label><input class="form-control" type="tel" name="user_phone" value="{{ $user->mobile_number }}"></div>
                <div class="col-md-6 form-group"><label class="field-label">تاريخ الميلاد</label><input class="form-control" type="date" name="birth_date" value="{{ $user->birth_date }}"></div>
                <div class="col-md-4 form-group"><label class="field-label">النوع</label><select class="form-control" name="gender" required><option value="male" @selected($user->gender === 'male')>ذكر</option><option value="female" @selected($user->gender === 'female')>أنثى</option></select></div>
                <div class="col-md-4 form-group"><label class="field-label">المجموعة الإدارية</label><select class="form-control" name="role" required>@foreach($roles as $role)<option value="{{ $role->id }}" @selected($user->role == $role->id)>{{ $role->name }}</option>@endforeach</select></div>
                <div class="col-md-4 form-group"><label class="field-label">الحالة</label><select class="form-control" name="status" required><option value="active" @selected($user->status === 'active')>نشط</option><option value="deactive" @selected($user->status === 'deactive')>غير نشط</option></select></div>
                <div class="col-md-6 form-group"><label class="field-label">كلمة مرور جديدة <span class="text-muted font-weight-normal">(اختياري)</span></label><input class="form-control" type="password" name="password" minlength="8" autocomplete="new-password"></div>
                <div class="col-md-6 form-group"><label class="field-label">تأكيد كلمة المرور</label><input class="form-control" type="password" name="password_confirmation" minlength="8" autocomplete="new-password"></div>
            </div></div><div class="modal-footer"><button class="btn btn-primary" type="submit">حفظ التعديلات</button><button class="btn btn-light" type="button" data-dismiss="modal">إلغاء</button></div>
        </form></div></div></div>
        @endcan
        @can('users.delete')
        @if(auth()->id() !== $user->id)<div class="modal fade" id="deleteUser{{ $user->id }}" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content"><form action="{{ route('deleteUser', $user->id) }}" method="POST">@csrf @method('DELETE')<div class="modal-header"><h5 class="modal-title">حذف المستخدم</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div><div class="modal-body">هل تريد حذف حساب <strong>{{ $user->name }}</strong>؟</div><div class="modal-footer"><button class="btn btn-danger" type="submit">حذف الحساب</button><button class="btn btn-light" type="button" data-dismiss="modal">إلغاء</button></div></form></div></div></div>@endif
        @endcan
    @endforeach
    @endcanany

    @can('users.create')
    <div class="modal fade" id="addUser" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form method="POST" action="{{ route('add_users') }}">@csrf
        <div class="modal-header"><h5 class="modal-title">إضافة مستخدم جديد</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <div class="modal-body"><div class="alert alert-light border small">سيتمكن المستخدم من الوصول إلى أقسام النظام حسب صلاحيات المجموعة المختارة.</div><div class="row">
            <div class="col-md-6 form-group"><label class="field-label">الاسم الكامل</label><input class="form-control" name="user_name" value="{{ old('user_name') }}" required minlength="2" maxlength="255"></div>
            <div class="col-md-6 form-group"><label class="field-label">البريد الإلكتروني</label><input class="form-control" type="email" name="email" value="{{ old('email') }}" required></div>
            <div class="col-md-6 form-group"><label class="field-label">كلمة المرور</label><input class="form-control" type="password" name="password" required minlength="8" autocomplete="new-password"></div>
            <div class="col-md-6 form-group"><label class="field-label">تأكيد كلمة المرور</label><input class="form-control" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></div>
            <div class="col-md-6 form-group"><label class="field-label">رقم الهاتف</label><input class="form-control" type="tel" name="user_phone" value="{{ old('user_phone') }}"></div>
            <div class="col-md-6 form-group"><label class="field-label">تاريخ الميلاد</label><input class="form-control" type="date" name="birth_date" value="{{ old('birth_date') }}"></div>
            <div class="col-md-4 form-group"><label class="field-label">النوع</label><select class="form-control" name="gender" required><option value="">اختر النوع</option><option value="male">ذكر</option><option value="female">أنثى</option></select></div>
            <div class="col-md-4 form-group"><label class="field-label">المجموعة الإدارية</label><select class="form-control" name="role" required><option value="">اختر المجموعة</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
            <div class="col-md-4 form-group"><label class="field-label">الحالة</label><select class="form-control" name="status" required><option value="active">نشط</option><option value="deactive">غير نشط</option></select></div>
        </div></div><div class="modal-footer"><button class="btn btn-primary" type="submit">إنشاء الحساب</button><button class="btn btn-light" type="button" data-dismiss="modal">إلغاء</button></div>
    </form></div></div></div>
    @endcan
</div>
@endsection

@section('js')
<script>
document.getElementById('userSearch')?.addEventListener('input', function () {
    const term = this.value.trim().toLocaleLowerCase();
    document.querySelectorAll('#usersTable tbody tr.user-row').forEach(row => {
        row.hidden = !row.innerText.toLocaleLowerCase().includes(term);
    });
});
</script>
@endsection
