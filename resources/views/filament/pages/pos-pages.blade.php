<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        @keyframes modal-appear {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-modal-appear {
            animation: modal-appear 0.3s ease-out forwards;
        }

    </style>

    @livewire('pos')

    <script>
        function paymentSuccessTimer() {
            return {
                seconds: 10,
                init() {
                    const interval = setInterval(() => {
                        this.seconds--;
                        if (this.seconds <= 0) {
                            clearInterval(interval);
                            this.$wire.set('showConfirmationModal', false);
                        }
                    }, 1000);
                }
            }
        }
    </script>

    {{-- Script untuk USB Print Service (Flask server) --}}
    <script src="{{ asset('js/usb-print-service.js') }}"></script>

    {{-- Script untuk Bluetooth Print --}}
    <script src="{{ asset('js/printer-thermal.js') }}"></script>

    {{-- Script untuk Layar Fullscreen --}}
    <script src="{{ asset('js/full-screen.js') }}"></script>

    {{-- Hide Filament page header for POS page (remove top green bar) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            try {
                const el = Array.from(document.querySelectorAll('h1,h2,h3')).find(h => {
                    const t = h.textContent.trim().toLowerCase();
                    return t === 'pos page' || t === 'halaman kasir';
                });
                if (el) {
                    const header = el.closest('header') || el.closest('[role="banner"]') || el.parentElement;
                    if (header) header.style.display = 'none';
                }
            } catch (e) {
                console.error('Hide POS header script error', e);
            }
        });
    </script>

</x-filament-panels::page>
