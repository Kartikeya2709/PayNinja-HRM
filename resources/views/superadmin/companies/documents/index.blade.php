@extends('layouts.app')

@section('title', 'Company Documents')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Company Documents - {{ $company->name }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('superadmin.companies.index') }}">Companies</a></div>
                <div class="breadcrumb-item active">Documents</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Upload New Document</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('superadmin.companies.documents.upload', $company) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        <label>Document Type</label>
                                        <select name="document_type" class="form-control @error('document_type') is-invalid @enderror">
                                            <option value="">Select Document Type</option>
                                            @foreach($documentTypes as $key => $label)
                                                <option value="{{ $key }}" {{ old('document_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('document_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Document File</label>
                                        <input type="file" name="document" accept="application/pdf, image/*" class="form-control @error('document') is-invalid @enderror">
                                        <small class="form-text text-muted">
                                            Allowed file types: PDF, JPG, JPEG, PNG (Max: 10MB)
                                        </small>
                                        @error('document')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-4">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Upload Document</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4>Company Documents</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Original Filename</th>
                                            <th>Status</th>
                                            <th>Uploaded By</th>
                                            <th>Upload Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($documents as $document)
                                            <tr>
                                                <td>{{ $documentTypes[$document->document_type] }}</td>
                                                <td>{{ $document->original_filename }}</td>
                                                <td>
                                                    @if($document->status === 'pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                    @elseif($document->status === 'verified')
                                                        <span class="badge badge-success">Verified</span>
                                                    @else
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @endif
                                                </td>
                                                <td>{{ $document->uploadedBy->name }}</td>
                                                <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('superadmin.companies.documents.show', [$company, $document]) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="View Document">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        
                                                        <a href="{{ Storage::url($document->file_path) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           target="_blank"
                                                           title="Download Document">
                                                            <i class="fas fa-download"></i>
                                                        </a>

                                                        @if($document->status === 'pending')
                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-success"
                                                                    title="Verify Document"
                                                                    onclick="verifyDocument('{{ $document->id }}')">
                                                                <i class="fas fa-check"></i>
                                                            </button>

                                                            <button type="button"
                                                                    class="btn btn-sm btn-outline-warning"
                                                                    title="Reject Document"
                                                                    onclick="showRejectModal('{{ $document->id }}')">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        @endif

                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Delete Document"
                                                                onclick="deleteDocument('{{ $document->id }}')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No documents uploaded yet.</td>
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
    </section>

    <!-- Reject Document Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Document</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Rejection Reason</label>
                            <textarea name="notes" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reject Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showRejectModal(documentId) {
        const modal = $('#rejectModal');
        const form = $('#rejectForm');
        form.attr('action', `{{ route('superadmin.companies.documents.reject', [$company->id, ':documentId']) }}`.replace(':documentId', documentId));
        modal.modal('show');
    }

    // Show toast notification
    function showToast(title, message, type) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: type,
            title: title,
            text: message
        });
    }

    function verifyDocument(documentId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to verify this document?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, verify it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ route('superadmin.companies.documents.verify', [$company->id, ':documentId']) }}`.replace(':documentId', documentId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                }).then(response => {
                    if (response.ok) {
                        showToast('Success', 'Document verified successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error', 'Failed to verify document', 'error');
                    }
                }).catch(error => {
                    showToast('Error', 'An error occurred: ' + error.message, 'error');
                });
            }
        });
    }

    function deleteDocument(documentId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ route('superadmin.companies.documents.destroy', [$company->id, ':documentId']) }}`.replace(':documentId', documentId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                }).then(response => {
                    if (response.ok) {
                        showToast('Deleted', 'Document deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error', 'Failed to delete document', 'error');
                    }
                }).catch(error => {
                    showToast('Error', 'An error occurred: ' + error.message, 'error');
                });
            }
        });
    }
</script>
@endpush