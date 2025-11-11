@extends('layouts.app')

@section('title', 'Discount Management')

@push('style')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .table-responsive { overflow-x: auto; }
        .discount-code {
            font-family: monospace;
            font-weight: bold;
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .status-active { color: green; }
        .status-expired { color: red; }
        .status-used { color: orange; }
    </style>
@endpush

@section('content')
    <div class="main-content-01">
        <div class="container">
            <section class="section">
                <div class="section-header">
                    <h1>Discount Management</h1>
                    <div class="section-header-breadcrumb">
                        <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                        <div class="breadcrumb-item active">Discounts</div>
                    </div>
                </div>

                @include('partials.alerts')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>All Discounts</h4>
                                <div class="card-header-action">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createDiscountModal">
                                        <i class="fas fa-plus"></i> Create Discount
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <form method="GET" action="{{ route('superadmin.discounts.index') }}">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control" placeholder="Search discount codes..." value="{{ request('search') }}">
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="col-md-6">
                                        <form method="GET" action="{{ route('superadmin.discounts.index') }}">
                                            <div class="input-group">
                                                <select name="status" class="form-control">
                                                    <option value="">All Status</option>
                                                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                                    <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Used</option>
                                                </select>
                                                <div class="input-group-append">
                                                    <button class="btn btn-outline-secondary" type="submit">Filter</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Code</th>
                                                <th>Type</th>
                                                <th>Value</th>
                                                <th>Usage Limit</th>
                                                <th>Used</th>
                                                <th>Valid Until</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($discounts ?? [] as $discount)
                                                <tr>
                                                    <td><span class="discount-code">{{ $discount->code }}</span></td>
                                                    <td>{{ ucfirst($discount->type) }}</td>
                                                    <td>
                                                        @if($discount->type == 'percentage')
                                                            {{ $discount->value }}%
                                                        @else
                                                            ₹ {{ number_format($discount->value, 2) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $discount->usage_limit ?: 'Unlimited' }}</td>
                                                    <td>{{ $discount->used_count }}</td>
                                                    <td>{{ $discount->valid_until ? $discount->valid_until->format('M d, Y') : 'No expiry' }}</td>
                                                    <td>
                                                        @if($discount->is_active && (!$discount->valid_until || $discount->valid_until->isFuture()) && (!$discount->usage_limit || $discount->used_count < $discount->usage_limit))
                                                            <span class="badge badge-success">Active</span>
                                                        @elseif($discount->valid_until && $discount->valid_until->isPast())
                                                            <span class="badge badge-danger">Expired</span>
                                                        @elseif($discount->usage_limit && $discount->used_count >= $discount->usage_limit)
                                                            <span class="badge badge-warning">Used Up</span>
                                                        @else
                                                            <span class="badge badge-secondary">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-info" onclick="viewDiscount({{ $discount->id }})">
                                                            <i class="fas fa-eye"></i> View
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-warning" onclick="editDiscount({{ $discount->id }})">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <form action="{{ route('superadmin.discounts.destroy', $discount) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this discount?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center">No discounts found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if(isset($discounts) && $discounts->hasPages())
                                    <div class="d-flex justify-content-center">
                                        {{ $discounts->appends(request()->query())->links() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Create Discount Modal -->
    <div class="modal fade" id="createDiscountModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Discount</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('superadmin.discounts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="code">Discount Code</label>
                                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type">Discount Type</label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="percentage" {{ old('type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                        <option value="fixed" {{ old('type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="value">Discount Value</label>
                                    <input type="number" step="0.01" name="value" id="value" class="form-control @error('value') is-invalid @enderror" value="{{ old('value') }}" required>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6" id="currency-group" style="display: none;">
                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <select name="currency" id="currency" class="form-control">
                                        <option value="USD" {{ old('currency', 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                                        <option value="INR" {{ old('currency') == 'INR' ? 'selected' : '' }}>INR</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="usage_limit">Usage Limit (Optional)</label>
                                    <input type="number" name="usage_limit" id="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit') }}" placeholder="Leave empty for unlimited">
                                    @error('usage_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="valid_until">Valid Until (Optional)</label>
                                    <input type="date" name="valid_until" id="valid_until" class="form-control @error('valid_until') is-invalid @enderror" value="{{ old('valid_until') }}">
                                    @error('valid_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Discount</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // View discount function
            window.viewDiscount = function(id) {
                // Implement view functionality
                window.location.href = '{{ route("superadmin.discounts.show", ":id") }}'.replace(':id', id);
            };

            // Edit discount function
            window.editDiscount = function(id) {
                // Implement edit functionality
                window.location.href = '{{ route("superadmin.discounts.edit", ":id") }}'.replace(':id', id);
            };

            const $typeSelect = $('#type');
            const $currencyGroup = $('#currency-group');

            $typeSelect.on('change', function() {
                if ($(this).val() === 'fixed') {
                    $currencyGroup.show();
                } else {
                    $currencyGroup.hide();
                }
            });

            // Trigger change event on page load if editing
            $typeSelect.trigger('change');
        });
    </script>
@endpush