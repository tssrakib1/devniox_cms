@extends('layouts.admin')
@section('title', 'Create Role')
@section('heading', 'Create Role')
@section('content')
<form method="post" action="{{ route('admin.roles.store') }}">
    @csrf
    <div class="card"><div class="card-body">@include('admin.roles._form')</div><div class="card-footer"><button class="btn btn-primary">Create role</button></div></div>
</form>
<script>
document.getElementById('copy_role_id')?.addEventListener('change', function () {
    const ids = JSON.parse(this.selectedOptions[0]?.dataset.permissions || '[]').map(String);
    document.querySelectorAll('input[name="permissions[]"]').forEach((input) => { input.checked = ids.includes(input.value); });
});
</script>
@endsection
