@extends('layouts.app')

@section('title', 'Edit Payroll - ID: {{ $payroll->id }}')

@section('content')
<div class="container">
    <section class="section">
        <div class="section-header">
            <h1 class="mb-0">Edit Payroll</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('home') }}">Dashboard</a></div>
                {{-- <div class="breadcrumb-item active"><a href="{{ route('admin.payroll.index') }}">Payroll Records</a></div> --}}
                {{-- <div class="breadcrumb-item active"><a href="{{ route('admin.payroll.show', $payroll->id) }}">Payroll #{{ $payroll->id }}</a></div> --}}
                <div class="breadcrumb-item">Edit</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                {{-- @if(\App\Models\User::hasAccess('Payroll Edit')) --}}
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Edit Payroll for {{ $payroll->employee->user->name ?? 'Unknown Employee' }}</h5>
                        {{-- <a href="{{ route('admin.payroll.show', $payroll->id) }}" class="btn btn-secondary btn-sm">Back to Payroll</a> --}}
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Employee Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Name:</strong> {{ $payroll->employee->user->name ?? 'Unknown Employee' }}</p>
                                        <p><strong>Employee ID:</strong> {{ $payroll->employee->employee_id ?? 'N/A' }}</p>
                                        <p><strong>Department:</strong> {{ $payroll->employee->department->name ?? 'N/A' }}</p>
                                        <p><strong>Designation:</strong> {{ $payroll->employee->designation->name ?? 'N/A' }}</p>
                                        {{-- <p><strong>Pay Period:</strong> {{ $payroll->pay_period_start->format('M d, Y') }} - {{ $payroll->pay_period_end->format('M d, Y') }}</p> --}}
                                        <p><strong>Status:</strong> <span class="badge badge-{{ $payroll->status == 'paid' ? 'success' : ($payroll->status == 'processed' ? 'info' : 'warning') }}">{{ ucfirst($payroll->status) }}</span></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Current Totals</h6>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Gross Salary:</strong> <span id="gross-total">{{ number_format($payroll->gross_salary, 2) }}</span></p>
                                        <p><strong>Total Deductions:</strong> <span id="deductions-total">{{ number_format($payroll->total_deductions, 2) }}</span></p>
                                        <p><strong>Net Salary:</strong> <span id="net-total">{{ number_format($payroll->net_salary, 2) }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form id="payroll-edit-form" action="{{ route('update', $payroll->id) }}" method="POST">

                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Earnings & Reimbursements</h5>
                                    <div id="earnings-container">
                                        @php $earningIndex = 0; @endphp
                                        @foreach($payroll->items->whereIn('type', ['earning', 'reimbursement']) as $item)
                                        <div class="card mb-2 earning-item" data-type="earning">
                                            <div class="card-body p-2">
                                                <div class="row align-items-center">
                                                    <div class="col-md-5">
                                                        <input type="text" name="items[{{ $earningIndex }}][description]" class="form-control form-control-sm" value="{{ $item->description }}" placeholder="Description" required>
                                                        <input type="hidden" name="items[{{ $earningIndex }}][type]" value="{{ $item->type }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="number" name="items[{{ $earningIndex }}][amount]" class="form-control form-control-sm amount-input" value="{{ $item->amount }}" step="0.01" min="0" placeholder="Amount" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="items[{{ $earningIndex }}][is_taxable]" class="form-check-input" value="1" {{ $item->is_taxable ? 'checked' : '' }}>
                                                            <label class="form-check-label">Taxable</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger btn-sm remove-item">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $earningIndex++; @endphp
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm mb-3" id="add-earning">
                                        <i class="fas fa-plus"></i> Add Earning
                                    </button>
                                </div>

                                <div class="col-md-6">
                                    <h5>Deductions</h5>
                                    <div id="deductions-container">
                                        @php $deductionIndex = 100; @endphp
                                        @foreach($payroll->items->where('type', 'deduction') as $item)
                                        <div class="card mb-2 deduction-item" data-type="deduction">
                                            <div class="card-body p-2">
                                                <div class="row align-items-center">
                                                    <div class="col-md-6">
                                                        <input type="text" name="items[{{ $deductionIndex }}][description]" class="form-control form-control-sm" value="{{ $item->description }}" placeholder="Description" required>
                                                        <input type="hidden" name="items[{{ $deductionIndex }}][type]" value="deduction">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <input type="number" name="items[{{ $deductionIndex }}][amount]" class="form-control form-control-sm amount-input" value="{{ $item->amount }}" step="0.01" min="0" placeholder="Amount" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger btn-sm remove-item">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @php $deductionIndex++; @endphp
                                        @endforeach
                                    </div>
                                    <button type="button" class="btn btn-warning btn-sm mb-3" id="add-deduction">
                                        <i class="fas fa-plus"></i> Add Deduction
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Optional notes about this payroll">{{ $payroll->notes }}</textarea>
                            </div>

                            <div class="mt-4 text-center">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Payroll
                                </button>
                                <a href="{{ route('show', $payroll) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            {{-- @else --}}
                <div class="alert alert-warning">
                    You do not have permission to edit payrolls.
                </div>
            {{-- @endif --}}
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let earningIndex = {{ $payroll->items->whereIn('type', ['earning', 'reimbursement'])->count() }};
    let deductionIndex = {{ $payroll->items->where('type', 'deduction')->count() + 100 }};

    // Add earning item
    document.getElementById('add-earning').addEventListener('click', function() {
        const container = document.getElementById('earnings-container');
        const itemHtml = `
            <div class="card mb-2 earning-item" data-type="earning">
                <div class="card-body p-2">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <input type="text" name="items[${earningIndex}][description]" class="form-control form-control-sm" placeholder="Description" required>
                            <input type="hidden" name="items[${earningIndex}][type]" value="earning">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="items[${earningIndex}][amount]" class="form-control form-control-sm amount-input" step="0.01" min="0" placeholder="Amount" required>
                        </div>
                        <div class="col-md-2">
                            <div class="form-check">
                                <input type="checkbox" name="items[${earningIndex}][is_taxable]" class="form-check-input" value="1">
                                <label class="form-check-label">Taxable</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
        earningIndex++;
        updateTotals();
    });

    // Add deduction item
    document.getElementById('add-deduction').addEventListener('click', function() {
        const container = document.getElementById('deductions-container');
        const itemHtml = `
            <div class="card mb-2 deduction-item" data-type="deduction">
                <div class="card-body p-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <input type="text" name="items[${deductionIndex}][description]" class="form-control form-control-sm" placeholder="Description" required>
                            <input type="hidden" name="items[${deductionIndex}][type]" value="deduction">
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="items[${deductionIndex}][amount]" class="form-control form-control-sm amount-input" step="0.01" min="0" placeholder="Amount" required>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger btn-sm remove-item">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
        deductionIndex++;
        updateTotals();
    });

    // Remove item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
            e.target.closest('.card').remove();
            updateTotals();
        }
    });

    // Update totals when amounts change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('amount-input')) {
            updateTotals();
        }
    });

    function updateTotals() {
        let grossTotal = 0;
        let deductionsTotal = 0;

        // Calculate earnings
        document.querySelectorAll('.earning-item .amount-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            grossTotal += value;
        });

        // Calculate deductions
        document.querySelectorAll('.deduction-item .amount-input').forEach(input => {
            const value = parseFloat(input.value) || 0;
            deductionsTotal += value;
        });

        const netTotal = grossTotal - deductionsTotal;

        document.getElementById('gross-total').textContent = grossTotal.toFixed(2);
        document.getElementById('deductions-total').textContent = deductionsTotal.toFixed(2);
        document.getElementById('net-total').textContent = netTotal.toFixed(2);
    }

    // Initialize totals on page load
    updateTotals();
});
</script>
@endpush
