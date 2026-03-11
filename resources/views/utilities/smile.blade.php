<x-app-layout>
    <title>Baya Jidda - Buy Smile Data</title>
    {{-- Custom CSS for active state --}}
    @push('styles')
    <style>
        .network-option {
            cursor: pointer;
            padding: 10px;
            border: 2px solid transparent;
            border-radius: 10px;
            transition: all 0.2s ease-in-out;
        }
        .network-option:hover {
            background-color: #f8f9fa;
        }
        .network-option.active {
            border-color: #df6808ff; /* Bootstrap primary color */
            background-color: #e7f1ff;
        }
        .small-note {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
    @endpush

    <div class="row justify-content-center mt-3">
        <div class="col-xl-6 col-lg-8 col-md-10 col-sm-12 mb-3">
            <div class="card custom-card shadow-sm border-0">
                <div class="card-header justify-content-between bg-primary text-white rounded-top">
                    <div class="card-title fw-semibold">
                        <i class="bi bi-wifi me-2"></i> Buy Smile Data
                    </div>
                </div>

                <div class="card-body">
                    <!-- Alerts -->
                    <div class="mb-4">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show text-center">{!! session('success') !!}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show text-center">{{ session('error') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <ul class="mb-0 ps-3 small">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Smile Form -->
                    <form id="buySmileForm" method="POST" action="{{ route('smile.buy') }}">
                        @csrf

                        <!-- Account Type -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Account Type</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="actype" id="actypePhone" value="PhoneNumber" checked>
                                    <label class="form-check-label" for="actypePhone">
                                        Phone Number
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="actype" id="actypeAccount" value="AccountNumber">
                                    <label class="form-check-label" for="actypeAccount">
                                        Account Number
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile/Account Number -->
                        <div class="mb-3">
                            <label for="mobileno" class="form-label fw-semibold" id="mobilenoLabel">Smile Phone/Account Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                <input type="text" id="mobileno" name="mobileno" value="{{ old('mobileno') }}" class="form-control" placeholder="Enter number" required>
                            </div>
                        </div>

                        <!-- Plans -->
                        <div class="mb-4">
                            <label for="plan_id" class="form-label d-flex justify-content-between align-items-center fw-semibold">
                                <span>Select Smile Bundle</span>
                                <small class="text-muted">
                                    Balance:
                                    <strong class="text-success">
                                        ₦{{ number_format($wallet->balance ?? 0, 2) }}
                                    </strong>
                                </small>
                            </label>
                            <select name="plan_id" id="plan_id" class="form-select form-select-lg" required>
                                <option value="" disabled selected>-- Select a Plan --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->plan_id }}" data-price="{{ $plan->price }}" @if(old('plan_id') == $plan->plan_id) selected @endif>
                                        {{ $plan->name }} - ₦{{ number_format($plan->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="button" id="buy-smile" class="btn btn-primary btn-lg fw-semibold">
                                <i class="bi bi-lightning-charge me-2"></i> Buy Now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PIN Confirmation Modal --}}
    <div class="modal fade" id="pinModal" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white fw-semibold" id="pinModalLabel">
                        <i class="bi bi-shield-lock-fill me-2"></i> Confirm Transaction PIN
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <h5 class="fw-bold">Transaction Summary</h5>
                        <p class="mb-1">Plan: <span id="summary-name" class="fw-semibold"></span></p>
                        <p class="mb-1" id="summary-type-label">Number: <span id="summary-number" class="fw-semibold"></span></p>
                        <p class="mb-3">Amount: <span id="summary-price" class="text-danger fw-bold"></span></p>
                    </div>

                    <p class="text-muted mb-3 small">
                        Please enter your <strong>5-digit PIN</strong> to confirm this transaction.
                    </p>
                    <div class="d-flex justify-content-center">
                        <input type="password" name="pin" id="pinInput" class="form-control text-center fw-bold fs-3 py-3 border-2 border-primary rounded-pill shadow-sm w-50" maxlength="5" inputmode="numeric" placeholder="•••••" required style="letter-spacing: 10px; font-family: 'Courier New', monospace;">
                    </div>
                    <small id="pinError" class="text-danger d-none mt-2 d-block fw-semibold">Incorrect PIN. Please try again.</small>
                </div>

                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmPinBtn" class="btn btn-primary px-4 rounded-pill fw-semibold">
                        Confirm & Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
  <div class="container-fluid px-4 mt-4">

     @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const buyButton = document.getElementById('buy-smile');
                const confirmButton = document.getElementById('confirmPinBtn');
                const form = document.getElementById('buySmileForm');
                const planSelect = document.getElementById('plan_id');
                const phoneInput = document.getElementById('mobileno');
                const actypeRadios = document.querySelectorAll('input[name="actype"]');
                const mobilenoLabel = document.getElementById('mobilenoLabel');

                // Dynamic Label based on Radio choice
                actypeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if(this.value === 'AccountNumber') {
                            mobilenoLabel.textContent = "Smile Account Number";
                        } else {
                            mobilenoLabel.textContent = "Smile Phone Number";
                        }
                    });
                });

                // --- Handle Buy Click ---
                buyButton.addEventListener('click', function () {
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    // Populate summary
                    const selectedOption = planSelect.options[planSelect.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    const name = selectedOption.text.split(' - ')[0];
                    const actype = document.querySelector('input[name="actype"]:checked').value;

                    document.getElementById('summary-name').textContent = name;
                    document.getElementById('summary-number').textContent = phoneInput.value;
                    document.getElementById('summary-type-label').innerHTML = `${actype === 'AccountNumber' ? 'Account Number' : 'Phone Number'}: <span id="summary-number" class="fw-semibold">${phoneInput.value}</span>`;
                    document.getElementById('summary-price').textContent = '₦' + parseFloat(price).toLocaleString('en-US', {minimumFractionDigits: 2});

                    const pinModal = new bootstrap.Modal(document.getElementById('pinModal'));
                    pinModal.show();
                });

                // --- Confirm PIN & Prevent Double Click ---
                confirmButton.addEventListener('click', function () {
                    const pin = document.getElementById('pinInput').value;
                    const pinError = document.getElementById('pinError');

                    if(pin.length !== 5) {
                        pinError.classList.remove('d-none');
                        pinError.textContent = "PIN must be 5 digits.";
                        return;
                    }

                    this.disabled = true;
                    this.innerHTML = '<i class="bi bi-arrow-repeat spinner-border spinner-border-sm"></i> Verifying...';

                    fetch("{{ route('verify.pin') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({ pin })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.valid) {
                            form.submit();
                        } else {
                            pinError.classList.remove('d-none');
                            pinError.textContent = "Incorrect PIN. Please try again.";
                            this.disabled = false;
                            this.innerHTML = 'Confirm & Proceed';
                        }
                    })
                    .catch(() => {
                        alert("Network error, please try again.");
                        this.disabled = false;
                        this.innerHTML = 'Confirm & Proceed';
                    });
                });
            });
        </script>
    @endpush

</x-app-layout>
