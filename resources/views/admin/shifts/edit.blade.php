@extends('admin.shifts.form')

@section('content')
    @parent
    @section('card-title', 'Edit Shift: ' . $shift->name)
    @section('form-action', route('admin.shifts.update', $shift->id))
    @section('form-method', 'POST')
    @method('PUT')
@endsection
