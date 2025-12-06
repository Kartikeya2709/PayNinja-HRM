@extends('layouts.app')

@section('title', 'Manage Leave Types in Policy')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1>Manage Leave Types for "{{ $leavePolicy->name }}"</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('company.leave-policies.index') }}">Leave Policies</a></div>
                <div class="breadcrumb-item active"><a href="">Manage Leave Types</a></div>
            </div>
        </div>
        <section class="card">
            <div class="card-1 card-header">
                <h5 class="mb-0">Leave Types in Policy ({{ $leavePolicy->financialYear->name }})</h5>
                <div class="section-header-button">
                    @if(!empty($availableLeaveTypes))
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
                        Add Leave Type
                    </button>
                    @endif
                    <a href="{{ route('company.leave-policies.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12 px-0">
                        <div class="card">
                            <div class="card-body">
                                @if(session('success'))
                                <div class="alert alert-success alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert">
                                            <span>&times;</span>
                                        </button>
                                        {{ session('success') }}
                                    </div>
                                </div>
                                @endif

                                @if(session('error'))
                                <div class="alert alert-danger alert-dismissible show fade">
                                    <div class="alert-body">
                                        <button class="close" data-dismiss="alert">
                                            <span>&times;</span>
                                        </button>
                                        {{ session('error') }}
                                    </div>
                                </div>
                                @endif

                                @if($leavePolicy->leaveTypePolicies->isEmpty())
                                <div class="alert alert-info">
                                    <p>No leave types added to this policy yet.
                                    @if(!empty($availableLeaveTypes))
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
                                            Add one now
                                        </button>
                                    @else
                                        All leave types are already added to this policy or no active leave types exist.
                                    @endif
                                    </p>
                                </div>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-striped" id="leaveTypeTable">
                                        <thead>
                                            <tr>
                                                <th>Leave Type</th>
                                                <th>Description</th>
                                                <th>Allocated Days</th>
                                                <th>Minimum Days</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($leavePolicy->leaveTypePolicies as $leaveTypePolicy)
                                            <tr>
                                                <td>{{ $leaveTypePolicy->leaveType->name }}</td>
                                                <td>{{ $leaveTypePolicy->leaveType->description ?? '-' }}</td>
                                                <td>{{ $leaveTypePolicy->allocated_days }}</td>
                                                <td>{{ $leaveTypePolicy->min_days }}</td>
                                                <td>
                                                    @if($leaveTypePolicy->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                    @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                    <button type="button"
                                                        class="btn btn-outline-warning btn-sm action-btn rounded-end-0"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editLeaveTypeModal"
                                                        data-id="{{ $leaveTypePolicy->id }}"
                                                        data-allocated-days="{{ $leaveTypePolicy->allocated_days }}"
                                                        data-min-days="{{ $leaveTypePolicy->min_days }}"
                                                        data-is-active="{{ $leaveTypePolicy->is_active }}"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Edit">
                                                        <span class="btn-content">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                    </button>
                                                    <form action="{{ route('company.leave-type-policies.destroy', $leaveTypePolicy->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-outline-danger btn-sm action-btn rounded-start-0"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="Remove"
                                                            onclick="return confirm('Are you sure you want to remove this leave type from the policy?');">
                                                            <span class="btn-content">
                                                                <i class="fas fa-trash"></i>
                                                            </span>
                                                        </button>
                                                    </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</div>

<!-- Add Leave Type Modal -->
@if(!empty($availableLeaveTypes))
<div class="modal fade" id="addLeaveTypeModal" tabindex="-1" aria-labelledby="addLeaveTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeaveTypeModalLabel">Add Leave Type to Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('company.leave-policies.add-leave-type', $leavePolicy->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-4">
                        <label for="leave_type_id">Leave Type <span class="text-danger">*</span></label>
                        <select name="leave_type_id"
                                id="leave_type_id"
                                class="form-control @error('leave_type_id') is-invalid @enderror"
                                required>
                            <option value="">Select Leave Type</option>
                            @foreach($availableLeaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                            @endforeach
                        </select>
                        @error('leave_type_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="allocated_days">Allocated Days <span class="text-danger">*</span></label>
                        <input type="number"
                               name="allocated_days"
                               id="allocated_days"
                               class="form-control @error('allocated_days') is-invalid @enderror"
                               value="{{ old('allocated_days', 0) }}"
                               min="0"
                               required>
                        @error('allocated_days')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="min_days">Minimum Days</label>
                        <input type="number"
                               name="min_days"
                               id="min_days"
                               class="form-control @error('min_days') is-invalid @enderror"
                               value="{{ old('min_days', 0) }}"
                               min="0">
                        @error('min_days')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Edit Leave Type Modal -->
<div class="modal fade" id="editLeaveTypeModal" tabindex="-1" aria-labelledby="editLeaveTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLeaveTypeModalLabel">Edit Leave Type in Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editLeaveTypeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group mb-4">
                        <label for="edit_allocated_days">Allocated Days <span class="text-danger">*</span></label>
                        <input type="number"
                               name="allocated_days"
                               id="edit_allocated_days"
                               class="form-control"
                               min="0"
                               required>
                    </div>

                    <div class="form-group mb-4">
                        <label for="edit_min_days">Minimum Days</label>
                        <input type="number"
                               name="min_days"
                               id="edit_min_days"
                               class="form-control"
                               min="0">
                    </div>

                    <div class="form-group mb-4">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input"
                                   id="edit_is_active"
                                   name="is_active"
                                   value="1">
                            <label class="custom-control-label" for="edit_is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#leaveTypeTable').DataTable();

    // Handle edit modal
    $('#editLeaveTypeModal').on('show.bs.modal', function (e) {
        const button = $(e.relatedTarget);
        const id = button.data('id');
        const allocatedDays = button.data('allocated-days');
        const minDays = button.data('min-days');
        const isActive = button.data('is-active');

        $('#editLeaveTypeForm').attr('action', '/app/leave-type-policies/' + id);
        $('#edit_allocated_days').val(allocatedDays);
        $('#edit_min_days').val(minDays);
        $('#edit_is_active').prop('checked', isActive == 1);
    });
});
</script>
@endpush
