{{-- SweetAlert2 popup for reward redemption success --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Helper functions
    function numberWithDots(x) {
        return (x ?? 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function escapeHtml(unsafe) {
        return String(unsafe ?? '-')
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/\"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function showRewardSuccessPopup(data) {
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 not loaded');
            alert('Penukaran Berhasil!\nMember: ' + (data.member_name || '-') + '\nBarang: ' + (data.product_name || '-') + '\nSisa Poin: ' + (data.remaining_points ?? 0));
            return;
        }
        Swal.fire({
            toast: false,
            position: 'center',
            icon: 'success',
            title: 'Penukaran Berhasil',
            html: '<div style="text-align:left">' +
                '<p><strong>Member:</strong> ' + escapeHtml(data.member_name || '-') + '</p>' +
                '<p><strong>Barang:</strong> ' + escapeHtml(data.product_name || '-') + '</p>' +
                '<p><strong>Sisa Poin:</strong> ' + numberWithDots(data.remaining_points ?? 0) + ' poin</p>' +
                '</div>',
            confirmButtonText: 'OK',
            showConfirmButton: true,
            allowOutsideClick: false,
            allowEscapeKey: true,
        });
    }

    // Listen for Livewire v3 event
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('reward-redemption-success', (data) => {
            console.log('[reward-popup] Livewire event received', data);
            // Livewire v3 sends array, get first element
            const eventData = Array.isArray(data) ? data[0] : data;
            showRewardSuccessPopup(eventData);
        });
    });

    // Fallback: also listen on window (for dispatchBrowserEvent style)
    window.addEventListener('reward-redemption-success', (event) => {
        console.log('[reward-popup] Window event received', event.detail);
        showRewardSuccessPopup(event.detail || {});
    });
</script>

@php
    $rewardSuccess = session('reward_success');
@endphp

@if($rewardSuccess)
<script>
    // Session flash fallback (for full page reload scenarios)
    (function() {
        const data = @json($rewardSuccess);
        console.log('[reward-popup] Session flash data found', data);
        showRewardSuccessPopup(data);
    })();
</script>
@endif
