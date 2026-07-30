<div class="mb-4">
    <label class="form-label" for="name">Role name</label>
    @if(($role->slug ?? null) === 'administrator')
        <input type="hidden" name="name" value="{{ $role->name }}">
        <input class="form-control" id="name" value="{{ $role->name }}" disabled>
        <div class="form-text">Administrator is a protected system role and cannot be renamed.</div>
    @else
        <input class="form-control" id="name" name="name" required maxlength="80" value="{{ old('name', $role->name ?? '') }}">
    @endif
</div>

@unless(isset($role))
    <div class="mb-4">
        <label class="form-label" for="copy_role_id">Copy permissions from <span class="text-muted fw-normal">(optional)</span></label>
        <select class="form-select" id="copy_role_id" name="copy_role_id">
            <option value="">Start with selected permissions</option>
            @foreach($copyableRoles as $copyRole)
                @php($copyPermissions = $copyRole->slug === 'administrator' ? $permissionMatrix->flatten()->pluck('id')->values() : $copyRole->permissions->pluck('id')->values())
                <option value="{{ $copyRole->id }}" data-permissions='@json($copyPermissions)' @selected((string) old('copy_role_id') === (string) $copyRole->id)>{{ $copyRole->name }}</option>
            @endforeach
        </select>
        <div class="form-text">Selecting a role copies its complete permission set into the matrix below.</div>
    </div>
@endunless

<fieldset @disabled(($role->slug ?? null) === 'administrator')>
    <legend class="h5">Module permissions</legend>
    <p class="text-muted">Grant only the actions this role needs. Administrator always has full access.</p>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead><tr><th>Module</th><th>View</th><th>Create</th><th>Edit</th><th>Delete</th></tr></thead>
            <tbody>
            @php($selected = collect(old('permissions', isset($role) ? $role->permissions->pluck('id')->all() : []))->map(fn ($id) => (int) $id))
            @foreach($permissionMatrix as $module => $permissions)
                <tr><th class="text-capitalize">{{ str_replace('-', ' ', $module) }}</th>
                @foreach(['view', 'create', 'edit', 'delete'] as $action)
                    @php($permission = $permissions->firstWhere('action', $action))
                    <td>@if($permission)<div class="form-check"><input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="permission-{{ $permission->id }}" @checked($selected->contains($permission->id))><label class="visually-hidden" for="permission-{{ $permission->id }}">{{ $module }} {{ $action }}</label></div>@else<span class="text-muted">—</span>@endif</td>
                @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</fieldset>
