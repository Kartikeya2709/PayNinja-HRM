@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Lead</h3>
                    <div class="card-tools">
                        <a href="{{ route('company-admin.leads.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('company-admin.leads.update', $lead) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" 
                                value="{{ old('name', $lead->name) }}" required>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" 
                                value="{{ old('email', $lead->email) }}" required>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" 
                                value="{{ old('phone', $lead->phone) }}">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                @foreach(['new', 'contacted', 'qualified', 'lost'] as $status)
                                    <option value="{{ $status }}" {{ old('status', $lead->status) == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <div class="input-group">
                                <textarea name="message" id="message" rows="4" class="form-control @error('message') is-invalid @enderror">{{ old('message', $lead->message) }}</textarea>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info" id="enhanceMessage">
                                        <i class="fas fa-magic"></i> Enhance
                                    </button>
                                </div>
                            </div>
                            @error('message')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update Lead</button>
                            <a href="{{ route('company-admin.leads.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>

                        <!-- AI Enhancement Modal -->
                        <div class="modal fade" id="enhanceModal" tabindex="-1" role="dialog" aria-labelledby="enhanceModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="enhanceModalLabel">Enhanced Message Preview</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div id="loadingSpinner" class="text-center d-none">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Enhancing...</span>
                                            </div>
                                            <p>Enhancing your message...</p>
                                        </div>
                                        <div id="enhancedContent" class="d-none">
                                            <h6>Enhanced Version:</h6>
                                            <div class="form-group">
                                                <textarea id="enhancedMessage" class="form-control" rows="6" readonly></textarea>
                                            </div>
                                        </div>
                                        <div id="errorMessage" class="alert alert-danger d-none"></div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="useEnhanced">Use Enhanced Version</button>
                                    </div>
                                </div>
                            </div>
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
    const enhanceBtn = document.getElementById('enhanceMessage');
    const messageInput = document.getElementById('message');
    const enhancedMessage = document.getElementById('enhancedMessage');
    const useEnhancedBtn = document.getElementById('useEnhanced');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const enhancedContent = document.getElementById('enhancedContent');
    const errorMessage = document.getElementById('errorMessage');

    enhanceBtn.addEventListener('click', async function() {
        const currentMessage = messageInput.value.trim();
        
        if (!currentMessage) {
            alert('Please enter a message to enhance');
            return;
        }

        // Reset states
        loadingSpinner.classList.remove('d-none');
        enhancedContent.classList.add('d-none');
        errorMessage.classList.add('d-none');
        
        // Show modal
        $('#enhanceModal').modal('show');

        try {
            const response = await fetch('{{ route('company-admin.leads.enhance-message') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    message: currentMessage
                })
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP error! status: ${response.status}, details: ${errorText}`);
            }

            const data = await response.json();

            if (data.success && data.enhanced_message) {
                enhancedMessage.value = data.enhanced_message;
                loadingSpinner.classList.add('d-none');
                enhancedContent.classList.remove('d-none');
            } else {
                throw new Error(data.message || 'No enhanced message received');
            }
        } catch (error) {
            console.error('Enhancement error:', error);
            loadingSpinner.classList.add('d-none');
            errorMessage.classList.remove('d-none');
            errorMessage.textContent = `Error: ${error.message || 'Failed to enhance message. Please try again.'}`;
        }
    });

    useEnhancedBtn.addEventListener('click', function() {
        messageInput.value = enhancedMessage.value;
        $('#enhanceModal').modal('hide');
    });
});
</script>
@endpush