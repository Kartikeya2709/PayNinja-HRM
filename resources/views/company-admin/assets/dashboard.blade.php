@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Asset Dashboard</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Assets</h5>
                                    <h2>{{ $totalAssets }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Value</h5>
                                    <h2>₹{{ number_format($totalValue, 2) }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Available Assets</h5>
                                    <h2>{{ $availableAssets }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Assigned Assets</h5>
                                    <h2>{{ $assignedAssets }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytical Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-secondary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Utilization Rate</h5>
                                    <h2>{{ $utilizationRate }}%</h2>
                                    <small>Assets currently in use</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Overdue Returns</h5>
                                    <h2>{{ $overdueAssignments }}</h2>
                                    <small>Assignments past due date</small>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="col-md-3">
                            <div class="card bg-dark text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Avg Asset Age</h5>
                                    <h2>{{ $averageAge }}m</h2>
                                    <small>Months since purchase</small>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col-md-3">
                            <div class="card bg-light text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">This Month</h5>
                                    <h2>{{ $assignmentsThisMonth }}</h2>
                                    <small>New assignments</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Analytics -->
                    <div class="row mb-4">
                        <!-- Assets by Condition -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Assets by Condition</h5>
                                </div>
                                <div class="card-body">
                                    @if(count($assetsByCondition) > 0)
                                        @foreach($assetsByCondition as $condition => $count)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="text-capitalize">{{ $condition }}</span>
                                                <span class="badge badge-secondary">{{ $count }}</span>
                                            </div>
                                            <div class="progress mb-3">
                                                <div class="progress-bar bg-{{ $condition === 'good' ? 'success' : ($condition === 'fair' ? 'warning' : 'danger') }}"
                                                     style="width: {{ $totalAssets > 0 ? ($count / $totalAssets) * 100 : 0 }}%">
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No condition data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Assets by Category -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Assets by Category</h5>
                                </div>
                                <div class="card-body">
                                    @if(count($assetsByCategory) > 0)
                                        @foreach($assetsByCategory->take(5) as $category)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>{{ $category['category'] }}</span>
                                                <span class="badge badge-primary">{{ $category['count'] }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No category data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Department Assets -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Department Assets</h5>
                                </div>
                                <div class="card-body">
                                    @if(count($departmentAssets) > 0)
                                        @foreach($departmentAssets as $dept)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>{{ $dept['department'] }}</span>
                                                <span class="badge badge-info">{{ $dept['assets'] }}</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-muted">No department data available</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Employees with Assets -->
                    @if(count($mostAssignedEmployees) > 0)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Top Employees by Asset Assignments</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @foreach($mostAssignedEmployees as $employee)
                                        <div class="col-md-2 mb-3">
                                            <div class="card text-center">
                                                <div class="card-body">
                                                    <h6 class="card-title">{{ $employee->name }}</h6>
                                                    <h4 class="text-primary">{{ $employee->assignments_count }}</h4>
                                                    <small class="text-muted">Assets</small>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Asset Inventory -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Asset Inventory</h5>
                                    <a href="{{ route('assets.inventory') }}" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Asset Code</th>
                                                    <th>Name</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($assets as $asset)
                                                <tr>
                                                    <td>{{ $asset->asset_code }}</td>
                                                    <td>{{ $asset->name }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $asset->status === 'available' ? 'success' : 'primary' }}">
                                                            {{ ucfirst($asset->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">No assets found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employees with Assets -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Employees with Assets</h5>
                                    <a href="{{ route('assets.employees') }}" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Employee</th>
                                                    <th>Assets Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($employeesWithAssets as $employee)
                                                <tr>
                                                    <td>{{ $employee->name }}</td>
                                                    <td>{{ $employee->assignments->whereNull('returned_date')->count() }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="2" class="text-center">No employees with assets found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Assignments -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">Recent Assignments</h5>
                                    <a href="{{ route('recent.assets.assignments') }}" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Asset</th>
                                                    <th>Employee</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($recentAssignments as $assignment)
                                                <tr>
                                                    <td>{{ $assignment->asset->name }}</td>
                                                    <td>{{ $assignment->employee->name ?? 'N/A' }}</td>
                                                    <td>{{ $assignment->assigned_date->format('M d') }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">No recent assignments found.</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection