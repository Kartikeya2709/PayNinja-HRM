@extends('layouts.app')

@section('content')
<div class="section container">
    <div class="section-header">
        <h1>Regularization Request</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ url('/home') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">Regularization Request</a></div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header justify-content-center mb-3">
                    <h5>New Attendance Regularization Request</h5>
                </div>

                <div class="card-body">
                    <form action="{{ route('regularization.requests.store') }}" method="POST">
                        @csrf
                        <div id="regularization-forms">
                            <div class="regularization-entry mb-4">
                                <div class="form-group mb-4">
                                    <label>Date</label>
                                    <input type="date" name="entries[0][date]" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label>Check-in Time</label>
                                            <input type="time" name="entries[0][check_in]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label>Check-out Time</label>
                                            <input type="time" name="entries[0][check_out]" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label>Reason</label>
                                    <textarea name="entries[0][reason]" class="form-control" rows="2"
                                        required></textarea>
                                </div>
                            </div>

                        </div>
                        <div class="text-center">
                            <button type="button" id="add-entry" class="btn btn-secondary margin-bottom">Add Another
                                Date</button>
                            <button type="submit" class="btn btn-primary">Submit Requests</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let entryCount = 1;
    const maxEntries = 5;

    document.getElementById('add-entry').addEventListener('click', function() {
        if (entryCount >= maxEntries) {
            alert('You can only add up to 5 entries.');
            return;
        }

        const newEntry = document.createElement('div');
        newEntry.className = 'regularization-entry mb-4 border p-3';
        newEntry.innerHTML = `
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="entries[${entryCount}][date]" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Check-in Time</label>
                            <input type="time" name="entries[${entryCount}][check_in]" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Check-out Time</label>
                            <input type="time" name="entries[${entryCount}][check_out]" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Reason</label>
                    <textarea name="entries[${entryCount}][reason]" class="form-control" rows="2" required></textarea>
                </div>
                <button type="button" class="btn btn-danger remove-entry">Remove</button>
            `;

        document.getElementById('regularization-forms').appendChild(newEntry);
        entryCount++;
    });

    document.getElementById('regularization-forms').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-entry')) {
            e.target.closest('.regularization-entry').remove();
            entryCount--;
        }
    });
});
</script>
@endpush