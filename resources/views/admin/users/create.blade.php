@extends('layouts.admin')
@section('title', 'Create User')
@section('heading', 'Create User')
@section('content')
<form method="post" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="card"><div class="card-body">@include('admin.users._form')</div><div class="card-footer d-flex gap-2"><button class="btn btn-primary">Create user</button><a class="btn btn-outline-secondary" href="{{ route('admin.users.index') }}">Cancel</a></div></div>
</form>
@endsection
