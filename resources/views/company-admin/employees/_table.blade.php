@foreach($employees as $index => $employee)
<tr>
    <td>{{ $employees->firstItem() + $index }}</td>
    <td>
        <div class="d-flex align-items-center">
            <div class="me-3">
                @if($employee->profile_image)
                <img src="{{ asset('storage/' . $employee->profile_image) }}" alt="Profile" class="rounded-circle"
                    width="40" height="40" style="object-fit: cover;">
                @else
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                    style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                    {{ substr($employee->user->name, 0, 1) }}
                </div>
                @endif
            </div>
            <div>
                <div class="fw-bold">{{ $employee->user->name }}</div>
                <small class="text-muted">{{ $employee->employee_code ?? 'N/A' }}</small>
            </div>
        </div>
    </td>
    <td>{{ $employee->user->email }}</td>
    <td>{{ $employee->department->name ?? 'N/A' }}</td>
    <td>{{ $employee->designation->title ?? 'N/A' }}</td>
    <td>
        @php
        $roleClass = match($employee->user->role) {
        'admin' => 'text-primary border border-primary',
        'employee' => 'text-success border border-success',
        'company_admin' => 'text-warning border border-warning',
        default => 'text-secondary border border-secondary'
        };
        @endphp
        <span class="badge {{ $roleClass }}" style="background: none !important;">
            {{ ucfirst($employee->user->role_name) }}
        </span>
    </td>
    <td>
        <div class="btn-group btn-group-sm">
        <a href="{{ route('company-admin.employees.view', $employee->id) }}"
            class="btn btn-outline-info btn-sm action-btn" data-id="{{ $employee->id }}" data-bs-toggle="tooltip"
            data-bs-placement="top" title="View Employee" aria-label="View">
            <span class="btn-content">
                <i class="fas fa-eye"></i>
            </span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </a>

        <a href="{{ route('company-admin.employees.edit', $employee->id) }}"
            class="btn btn-outline-warning btn-sm action-btn" data-id="{{ $employee->id }}"
            data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Employee" aria-label="Edit">
            <span class="btn-content">
                <i class="fas fa-edit"></i>
            </span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        </a>

        @if($employee->user->role !== 'company_admin')
        <button type="button" class="btn btn-outline-primary btn-sm change-role-btn action-btn" data-bs-toggle="modal"
            data-bs-target="#roleModal" data-employee-id="{{ $employee->id }}"
            data-employee-name="{{ $employee->user->name }}" data-current-role="{{ $employee->user->role }}"
            data-update-url="{{ route('company-admin.employees.update-role', $employee->id) }}" title="Change Role">
            <i class="fas fa-user-edit"></i>
        </button>
        </div>
        @endif
    </td>
</tr>
@endforeach