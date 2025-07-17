@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="emp-profile">
        <h2>Employee Profile</h2>
        <div class="emp-profile-details">
        <p><b>Name:</b> {{ $employee->name }}</p>
        <p><b>Email:</b> {{ $employee->email }}</p>
        <p><b>Joined:</b> {{ $employee->created_at->format('Y-m-d') }}</p>
</div>
        <!-- Add other relevant employee information -->
    </div>
</div>
@endsection
