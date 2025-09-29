@extends('layouts.app')

@section('title', 'Document Details')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Document Details</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('superadmin.companies.index') }}">Companies</a></div>
                <div class="breadcrumb-item">
                    <a href="{{ route('superadmin.companies.show', $company) }}">{{ $company->name }}</a>
                </div>
                <div class="breadcrumb-item">
                    <a href="{{ route('superadmin.companies.documents.index', $company) }}">Documents</a>
                </div>
                <div class="breadcrumb-item active">Document Details</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Document Information</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-striped">
                                        <tr>
                                            <th>Document Type</th>
                                            <td>{{ $documentTypes[$document->document_type] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Original Filename</th>
                                            <td>{{ $document->original_filename }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($document->status === 'pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                @elseif($document->status === 'verified')
                                                    <span class="badge badge-success">Verified</span>
                                                @else
                                                    <span class="badge badge-danger">Rejected</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Uploaded By</th>
                                            <td>{{ $document->uploadedBy->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Upload Date</th>
                                            <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        @if($document->verified_at)
                                        <tr>
                                            <th>Verified At</th>
                                            <td>{{ $document->verified_at->format('M d, Y H:i') }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>Notes</th>
                                            <td>{{ $document->notes ?: 'No notes provided' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <div class="document-preview">
                                        @php
                                            $extension = pathinfo($document->original_filename, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif']);
                                        @endphp

                                        @if($isImage)
                                            <img src="{{ Storage::url($document->file_path) }}" 
                                                 alt="Document Preview" 
                                                 class="img-fluid">
                                        @else
                                            <div class="text-center">
                                                <i class="fas fa-file-pdf fa-5x text-danger"></i>
                                                <p class="mt-3">
                                                    <a href="{{ Storage::url($document->file_path) }}" 
                                                       class="btn btn-primary" 
                                                       target="_blank">
                                                        <i class="fas fa-download"></i> Download Document
                                                    </a>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="buttons">
                                        @if($document->status === 'pending')
                                            <form action="{{ route('superadmin.companies.documents.verify', [$company, $document]) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-success" 
                                                        onclick="return confirm('Are you sure you want to verify this document?')">
                                                    <i class="fas fa-check"></i> Verify Document
                                                </button>
                                            </form>

                                            <button type="button"
                                                    class="btn btn-warning"
                                                    onclick="showRejectModal()">
                                                <i class="fas fa-times"></i> Reject Document
                                            </button>
                                        @endif

                                        <form action="{{ route('superadmin.companies.documents.destroy', [$company, $document]) }}" 
                                              method="POST" 
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this document?')">
                                                <i class="fas fa-trash"></i> Delete Document
                                            </button>
                                        </form>

                                        <a href="{{ route('superadmin.companies.documents.index', $company) }}" 
                                           class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to Documents
                                        </a>
                                    </div>
                                </div>
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
                <form action="{{ route('superadmin.companies.documents.reject', [$company, $document]) }}" method="POST">
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
    function showRejectModal() {
        $('#rejectModal').modal('show');
    }
</script>
@endpush