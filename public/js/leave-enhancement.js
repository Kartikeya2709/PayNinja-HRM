function enhanceLeaveRequest() {
    // Get form values
    const leaveType = document.getElementById('leave_type_id');
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const reason = document.getElementById('reason').value;

    // Get the selected leave type text
    const leaveTypeText = leaveType.options[leaveType.selectedIndex].text;

    // Disable enhance button and show loading state
    const enhanceBtn = document.getElementById('enhance-btn');
    const originalBtnText = enhanceBtn.innerHTML;
    enhanceBtn.disabled = true;
    enhanceBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enhancing...';
    
    // Send to AI enhancement endpoint
    fetch('/employee/leave-requests/enhance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            leave_type: leaveTypeText,
            start_date: startDate,
            end_date: endDate,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('reason').value = data.message;
            
            // Show success notification
            Swal.fire({
                icon: 'success',
                title: 'Request Enhanced',
                text: 'Your leave request has been professionally enhanced!',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            throw new Error(data.message || 'Failed to enhance message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Enhancement Failed',
            text: error.message
        });
    })
    .finally(() => {
        // Reset button state
        enhanceBtn.disabled = false;
        enhanceBtn.innerHTML = originalBtnText;
    });
}