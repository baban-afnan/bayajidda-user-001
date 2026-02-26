<x-app-layout>
    <title>Smart Link - {{ $title ?? 'P2P Transfer' }}</title>

    <div class="container-fluid py-4 px-md-4">
        <!-- Page Header -->
        <div class="mb-4">
            <h1 class="h3 fw-bold text-gray-800 mb-1">P2P Fund Transfer</h1>
            <p class="text-muted small mb-0">Send funds instantly to other Smart Link users.</p>
        </div>

        @if(session('success') || session('error'))
            <div class="row mb-4">
                <div class="col-12">
                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm rounded-4">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm rounded-4">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="row g-4">
            <!-- Transfer Form -->
            <div class="col-xl-7 col-lg-12">
                <div class="card border-0 shadow-sm h-100 rounded-4 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0"><i class="fas fa-paper-plane me-2 text-primary"></i>Send Money</h5>
                        <div class="text-end">
                            <p class="text-muted small mb-0">Wallet Balance</p>
                            <h5 class="fw-bold text-primary mb-0">₦{{ number_format($wallet->balance ?? 0, 2) }}</h5>
                        </div>
                    </div>

                    <form id="p2pTransferForm" action="{{ route('p2p.transfer') }}" method="POST">
                        @csrf
                        <input type="hidden" name="pin" id="hidden_pin">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Recipient Email or Phone</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-start-3"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="recipient" id="recipient_input" class="form-control rounded-end-3 bg-light border-0 @error('recipient') is-invalid @enderror" 
                                           placeholder="user@example.com or 08012345678" value="{{ old('recipient') }}" required autocomplete="off">
                                </div>
                                <div id="recipient_status" class="mt-1 small"></div>
                                @error('recipient') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Amount (₦)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-start-3"><i class="fas fa-naira-sign text-muted"></i></span>
                                    <input type="number" name="amount" id="amount_input" class="form-control rounded-end-3 bg-light border-0 @error('amount') is-invalid @enderror" 
                                           placeholder="100.00" min="100" step="0.01" value="{{ old('amount') }}" required>
                                </div>
                                @error('amount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">Description (Optional)</label>
                                <textarea name="description" class="form-control rounded-3 bg-light border-0 @error('description') is-invalid @enderror" 
                                          placeholder="Split for dinner, Gift, etc." rows="2">{{ old('description') }}</textarea>
                                @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 pt-3">
                                <button type="button" id="initiateTransferBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                                    <i class="fas fa-paper-plane me-2"></i> Send Funds Now
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Transfer Info/Guidelines -->
            <div class="col-xl-5 col-lg-12">
                <div class="card border-0 shadow-sm h-100 rounded-4 p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-info-circle me-2 text-primary"></i>Transfer Guidelines</h5>
                    
                    <div class="d-flex flex-column gap-4">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">1</div>
                            <div>
                                <h6 class="fw-bold mb-1">Instant Delivery</h6>
                                <p class="text-muted small mb-0">P2P transfers are processed instantly and funds are available immediately in the recipient's wallet.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">2</div>
                            <div>
                                <h6 class="fw-bold mb-1">Zero Fees</h6>
                                <p class="text-muted small mb-0">Sending money to other Smart Link users is free of charge. No hidden costs.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">3</div>
                            <div>
                                <h6 class="fw-bold mb-1">Verify Recipient</h6>
                                <p class="text-muted small mb-0">Always double-check the recipient's email or phone number. Transfers are irreversible once completed.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto pt-5">
                        <div class="alert alert-warning border-0 rounded-4 small p-3 mb-0">
                            <i class="fas fa-shield-alt me-2"></i> Never share your transaction PIN with anyone. Our staff will never ask for it.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Recipient Verification Logic
        let verificationTimeout = null;
        const recipientInput = document.getElementById('recipient_input');
        const recipientStatus = document.getElementById('recipient_status');
        const initiateBtn = document.getElementById('initiateTransferBtn');

        recipientInput.addEventListener('input', function() {
            clearTimeout(verificationTimeout);
            const val = this.value.trim();
            
            if (val.length < 5) {
                recipientStatus.innerHTML = '';
                return;
            }

            recipientStatus.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i> Verifying...</span>';
            
            verificationTimeout = setTimeout(() => {
                fetch(`{{ route('p2p.verify') }}?identifier=${encodeURIComponent(val)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            recipientStatus.innerHTML = `<span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Recipient: ${data.name}</span>`;
                        } else {
                            recipientStatus.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle me-1"></i> ${data.message}</span>`;
                        }
                    })
                    .catch(() => {
                        recipientStatus.innerHTML = '<span class="text-danger">Verification failed</span>';
                    });
            }, 600);
        });

        // SweetAlert PIN Confirmation
        initiateBtn.addEventListener('click', function() {
            const recipient = recipientInput.value.trim();
            const amount = document.getElementById('amount_input').value;

            if (!recipient || !amount) {
                Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'Please enter recipient and amount.' });
                return;
            }

            Swal.fire({
                title: 'Confirm Transaction',
                html: `You are sending <b>₦${parseFloat(amount).toLocaleString()}</b> to <b>${recipient}</b>.<br><br>Enter your 5-digit PIN:`,
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    maxlength: 5,
                    autofocus: 'true',
                    style: 'text-align: center; letter-spacing: 1em; font-size: 1.5rem;'
                },
                showCancelButton: true,
                confirmButtonText: 'Confirm Transfer',
                confirmButtonColor: '#0d5c3e',
                showLoaderOnConfirm: true,
                preConfirm: (pin) => {
                    if (!pin || pin.length !== 5) {
                        Swal.showValidationMessage('Please enter your 5-digit PIN');
                        return false;
                    }
                    return pin;
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('hidden_pin').value = result.value;
                    document.getElementById('p2pTransferForm').submit();
                }
            });
        });

        // Alert for session notifications using Swal if available
        @if(session('success'))
            if(typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Success!', text: "{{ session('success') }}", timer: 3000, showConfirmButton: false });
            }
        @endif
        @if(session('error'))
            if(typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error!', text: "{{ session('error') }}" });
            }
        @endif
    </script>
    @endpush
</x-app-layout>
