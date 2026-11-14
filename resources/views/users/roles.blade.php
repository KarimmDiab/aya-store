@extends('layouts.master')

<title>معرض أيه - المجموعات والصلاحيات</title>

@section('css')
<style>
    .role-management{--rm-primary:#3157d5;--rm-ink:#17233c}
    .role-management .rm-hero{padding:25px 28px;border:0;border-radius:18px;color:#fff;background:linear-gradient(115deg,#263c91,#4268e8);box-shadow:0 12px 28px rgba(49,87,213,.16)}
    .role-management .rm-hero h2{color:#fff;font-weight:800;margin:0 0 7px}.role-management .rm-hero p{color:rgba(255,255,255,.78);margin:0}
    .role-management .rm-tabs{display:flex;gap:8px;margin:16px 0}.role-management .rm-tabs a{padding:9px 15px;border-radius:9px;color:#60708c;background:#fff;border:1px solid #e9edf5;font-weight:700}.role-management .rm-tabs a.active{color:#3157d5;background:#edf1ff;border-color:#dbe3ff}
    .role-management .rm-card{border:1px solid #edf0f7;border-radius:16px;box-shadow:0 8px 26px rgba(25,42,70,.05);overflow:hidden}.role-management .rm-card>.card-header{background:#fff;border-bottom:1px solid #edf0f7;padding:19px 22px}
    .role-management .rm-role{display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;margin-inline-end:9px;border-radius:12px;background:#edf1ff;color:#3157d5}
    .role-management .table th{color:#77839b;font-size:12px;border-top:0}.role-management .table td{vertical-align:middle}
    .role-management .modal-content{border:0;border-radius:16px;overflow:hidden}.role-management .modal-header{background:#f7f8fc}.role-management .modal-title{font-weight:800;color:#17233c}
    .role-management .form-control{border-radius:9px;min-height:41px}.role-management .permission-grid{border-radius:10px;overflow:hidden}.role-management .permission-grid th,.role-management .permission-grid td{padding:9px}
</style>
@endsection

@section('page-header')
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto"><h4 class="content-title mb-0">المستخدمين والصلاحيات <span class="text-muted">/ المجموعات الإدارية</span></h4></div>
</div>
@endsection

@section('content')
<div class="role-management">
<div class="col-xl-12">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="card rm-hero mb-3"><div class="d-flex flex-wrap align-items-center justify-content-between"><div><h2>المجموعات والصلاحيات</h2><p>خصص الوصول لكل مجموعة حسب مهام فريقك.</p></div>@can('users.create')<button class="btn btn-light mt-3 mt-md-0" type="button" data-toggle="modal" data-target="#addRole"><i class="fa fa-layer-group ml-1"></i> إنشاء مجموعة</button>@endcan</div></div>
    <div class="rm-tabs"><a href="{{ route('all_users') }}">المستخدمون</a><a class="active" href="{{ route('all_roles') }}">المجموعات والصلاحيات</a></div>
    <div class="card rm-card">
        <div class="card-header d-flex justify-content-between align-items-center"><div><h4 class="card-title mb-1">المجموعات الإدارية</h4><p class="text-muted mb-0">أنشئ مجموعة وحدد ما يمكن لأعضائها عرضه أو إضافته أو تعديله أو حذفه.</p></div></div>
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead><tr><th>المجموعة</th><th>الوصف</th><th>عدد المستخدمين</th><th>الصلاحيات</th><th>إجراءات</th></tr></thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td><span class="rm-role"><i class="fa fa-users"></i></span><strong>{{ $role->name }}</strong>@if(in_array($role->name, ['مدير النظام', 'Super admin'], true)) <span class="badge badge-primary mr-1">مدير النظام</span>@endif</td><td>{{ $role->description ?: '—' }}</td><td><span class="badge badge-light px-2 py-2">{{ $role->users_count }} مستخدم</span></td>
                        <td><span class="badge badge-info px-2 py-2">{{ $role->permissions->count() }} صلاحية</span></td>
                        <td class="text-nowrap">
                            @can('users.update')<button class="btn btn-sm btn-info" type="button" data-toggle="modal" data-target="#editRole{{ $role->id }}">تعديل</button>@endcan
                            @can('users.delete') @if(!in_array($role->name, ['مدير النظام', 'Super admin'], true))
                                <form action="{{ route('deleteroles', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('تأكيد حذف المجموعة؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" type="submit">حذف</button></form>
                            @endif @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">لا توجد مجموعات إدارية بعد.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @can('users.update')
    @foreach($roles as $role)
        <div class="modal fade" id="editRole{{ $role->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">تعديل مجموعة: {{ $role->name }}</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                <form action="{{ route('updateroles', $role->id) }}" method="POST">@csrf
                    <div class="modal-body">
                        <div class="form-group"><label>اسم المجموعة</label><input class="form-control" name="add_role" value="{{ $role->name }}" required maxlength="100"></div>
                        <div class="form-group"><label>الوصف</label><input class="form-control" name="role_desc" value="{{ $role->description }}" maxlength="255"></div>
                        @include('users.partials.permission_matrix', ['role' => $role])
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary" type="submit">حفظ الصلاحيات</button><button class="btn btn-secondary" type="button" data-dismiss="modal">إغلاق</button></div>
                </form>
            </div></div>
        </div>
    @endforeach
    @endcan
</div>

@can('users.create')
<div class="modal fade" id="addRole" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">إضافة مجموعة إدارية</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
        <form action="{{ route('add_roles') }}" method="POST">@csrf
            <div class="modal-body">
                <div class="form-group"><label>اسم المجموعة</label><input class="form-control" name="add_role" required maxlength="100"></div>
                <div class="form-group"><label>الوصف</label><input class="form-control" name="role_desc" maxlength="255"></div>
                @include('users.partials.permission_matrix', ['role' => null])
            </div>
            <div class="modal-footer"><button class="btn btn-primary" type="submit">إنشاء المجموعة</button><button class="btn btn-secondary" type="button" data-dismiss="modal">إغلاق</button></div>
        </form>
    </div></div>
</div>
@endcan
</div>
@endsection
