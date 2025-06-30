@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">New Attendance Regularization Request</div>

                <div class="card-body">
                    <form action="{{ route('regularization.requests.store') }}" method="POST">
                        @csrf
                        <div id="regularization-forms">
                            <div class="regularization-entry mb-4 border p-3">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" name="entries[0][date]" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Check-in Time</label>
                                            <input type="time" name="entries[0][check_in]" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Check-out Time</label>
                                            <input type="time" name="entries[0][check_out]" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Reason</label>
                                    <textarea name="entries[0][reason]" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="add-entry" class="btn btn-secondary">Add Another Date</button>
                        <button type="submit" class="btn btn-primary">Submit Requests</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let entryCount = 1;
        const maxEntries = 5;

        document.getElementById('add-entry').addEventListener('click', function () {
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

        document.getElementById('regularization-forms').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-entry')) {
                e.target.closest('.regularization-entry').remove();
                entryCount--; 
            }
        });
    });
</script>
@endpush
