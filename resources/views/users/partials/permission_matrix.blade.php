<div class="table-responsive mt-3">
    <table class="table table-bordered table-sm mb-0 permission-grid">
        <thead><tr><th>القسم</th>@foreach($actions as $actionLabel)<th class="text-center">{{ $actionLabel }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach($modules as $module => $moduleLabel)
            <tr>
                <th>{{ $moduleLabel }}</th>
                @foreach($actions as $action => $actionLabel)
                    @php($permissionName = $module.'.'.$action)
                    <td class="text-center"><label class="mb-0" aria-label="{{ $actionLabel }} {{ $moduleLabel }}"><input type="checkbox" name="permissions[]" value="{{ $permissionName }}" {{ $role && $role->hasPermissionTo($permissionName) ? 'checked' : '' }}></label></td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
    <small class="text-muted">إلغاء كل الخيارات يمنع الوصول إلى القسم، وتشمل صلاحية العرض صفحات القسم.</small>
</div>
