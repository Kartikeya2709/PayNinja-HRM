@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: yellow;">
    <h1 class="mb-0">(Under Development)</h1>
</div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Module Access Management</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('company-admin.module-access.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Admin Access</th>
                                        <th>Employee Access</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Define module labels for display
                                        $moduleLabels = [
                                            'leave' => 'Leave Management',
                                            'reimbursement' => 'Reimbursement',
                                            'team' => 'Team Management',
                                            'payroll' => 'Payroll Management',
                                            'attendance' => 'Attendance Management'
                                        ];
                                        
                                        // Define the order of roles
                                        $roles = ['admin', 'employee'];
                                    @endphp
                                    
                                    @foreach($modules as $moduleKey => $moduleAccess)
                                        <tr>
                                            <td>{{ $moduleLabels[$moduleKey] ?? ucfirst($moduleKey) }}</td>
                                            @foreach($roles as $role)
                                                <td>
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                               class="form-check-input" 
                                                               name="modules[{{ $moduleKey }}][{{ $role }}]" 
                                                               value="1" 
                                                               {{ $moduleAccess[$role] ?? false ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
