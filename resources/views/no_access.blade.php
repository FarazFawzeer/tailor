@extends('layouts.vertical', ['subtitle' => 'Welcome'])

@section('content')
    @include('layouts.partials.page-title', ['title' => 'Welcome', 'subtitle' => auth()->user()->name])

    <div class="card">
        <div class="card-body">
            <div class="alert alert-info mb-0">
                Your account doesn't have any screens assigned yet.
                <br>
                Please ask your administrator to give your account a role.
            </div>
        </div>
    </div>
@endsection
