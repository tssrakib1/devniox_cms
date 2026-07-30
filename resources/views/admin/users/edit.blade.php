@extends('layouts.admin')
@section('title', 'Edit User')
@section('heading', 'Edit User')
@section('content')
<form method="post" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="card"><div class="card-body">@include('admin.users._form')</div><div class="card-footer d-flex gap-2"><button class="btn btn-primary">Save changes</button><a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Cancel</a></div></div>
</form>
@endsection
