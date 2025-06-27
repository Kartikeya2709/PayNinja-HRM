@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 manage-shift">
        <h1 class="h3 mb-0 text-gray-800">Manage Shifts</h1>
        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Shift
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="shifts-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Timing</th>
                            <th>Grace Period</th>
                            <th>Break</th>
                            <th>Default</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shifts as $shift)
                            <tr>
                                <td>{{ $shift->name }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - 
                                    {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                    @if($shift->is_night_shift) 
                                        <span class="badge badge-info">Night Shift</span>
                                    @endif
                                </td>
                                <td>{{ $shift->grace_period_minutes }} minutes</td>
                                <td>
                                    @if($shift->has_break)
                                        {{ \Carbon\Carbon::parse($shift->break_start)->format('h:i A') }} - 
                                        {{ \Carbon\Carbon::parse($shift->break_end)->format('h:i A') }}
                                    @else
                                        No Break
                                    @endif
                                </td>
                                <td>
                                    @if($shift->is_default)
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.shifts.edit', $shift->id) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.shifts.destroy', $shift->id) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to delete this shift?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#shifts-table').DataTable({
            "order": [[0, 'asc']]
        });
    });
</script>
@endpush
