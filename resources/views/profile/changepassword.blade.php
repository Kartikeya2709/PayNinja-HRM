@extends('layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="main-content-01 container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-10 col-sm-12 profile-form-col">
            <div class="card profile-form">
                <div class="card-header mb-4">{{ __('Change Password') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group row mb-5">
                            @if (session('status'))
                                <div class="alert alert-success">
                                    {{ session('status') }}
                                </div>
                            @endif
                            <label for="current_password" class="col-md-12 col-form-label text-md-right">{{ __('Current Password') }}</label>

                            <div class="col-md-12">
                                <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required autocomplete="current_password">

                                @error('current_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row mb-5">
                            <label for="new_password" class="col-md-12 col-form-label text-md-right">{{ __('New Password') }}</label>

                            <div class="col-md-12">
                                <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" required autocomplete="new_password">

                                @error('new_password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row mb-5">
                            <label for="new_password_confirmation" class="col-md-12 col-form-label text-md-right">{{ __('Confirm New Password') }}</label>

                            <div class="col-md-12">
                                <input id="new_password_confirmation" type="password" class="form-control @error('new_password_confirmation') is-invalid @enderror" name="new_password_confirmation" required autocomplete="new_password_confirmation">

                                @error('new_password_confirmation')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Add more fields as necessary -->

                        <div class="form-group row mb-0">
                            <div class="col-md-12">
                                <button type="submit" class="btn button">
                                    {{ __('Change Password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection