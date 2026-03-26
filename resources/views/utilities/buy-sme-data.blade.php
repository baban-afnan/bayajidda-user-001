<x-app-layout>
    <title>Baya Jidda - SME Data Plans</title>

    <div class="row">
        <div class="col-xxl-12 col-xl-12">
            <div class="row mt-3">
                <div class="col-xl-6 mb-3">
                    <div class="card custom-card shadow-sm border-0">
                        <div class="card-header justify-content-between bg-success text-white rounded-top">
                            <div class="card-title fw-semibold">
                                <i class="ti ti-world me-2"></i> SME Data Service
                            </div>
                        </div>

                        <div class="card-body">
                            <center class="mb-3">
                                <img src="{{ asset('assets/img/apps/network_providers.png') }}"
                                     class="img-fluid mb-3 rounded-2"
                                     style="width: 45%; min-width: 120px;" alt="Network Providers">
                            </center>

                            <p class="text-center text-muted mb-4">
                                Select network, plan type, and your desired data bundle.
                            </p>

                            {{-- Flash Messages will be shown via SweetAlert below --}}

                            {{-- Buy SME Data Form --}}
                            <form id="buySmeDataForm" method="POST" action="{{ route('buy-sme-data.submit') }}">
                                @csrf

                                {{-- Network Selection --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Network</label>
                                    <select name="network" id="service_id" class="form-select text-center" required>
                                        <option value="">Choose Network</option>
                                        @foreach ($networks as $network)
                                            <option value="{{ $network->network }}">{{ strtoupper($network->network) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Data Type subselection (Gifting, SME, Corporate etc) --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Data Type</label>
                                    <select name="type" id="type" class="form-select text-center" required>
                                        <option value="">Select Type</option>
                                    </select>
                                </div>

                                {{-- Data Plan --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Data Plan</label>
                                    <select name="plan" id="plan" class="form-select text-center" required>
                                        <option value="">Select Plan</option>
                                    </select>
                                </div>

                                {{-- Amount --}}
                                <div class="mb-3 text-start">
                                    <label for="amountToPay" class="form-label fw-semibold d-flex justify-content-between">
                                        <span>Amount to Pay</span>
                                        <small class="text-muted">Balance: 
                                            <strong class="text-success">
                                                ₦{{ number_format($wallet->balance ?? 0, 2) }}
                                            </strong>
                                        </small>
                                    </label>
                                    <input type="text" id="amountToPay" name="amount" readonly class="form-control text-center bg-light fw-bold" placeholder="₦ 0.00" />
                                </div>

                                {{-- Phone Number --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Recipient Phone Number</label>
                                    <input type="text" id="mobileno" name="mobileno"
                                           class="form-control text-center"
                                           placeholder="08012345678"
                                           maxlength="11" required>
                                </div>

                                {{-- Submit --}}
                                <div class="d-grid mt-4">
                                    <button type="button" id="purchaseBtn" class="btn btn-primary btn-lg fw-semibold">
                                        Purchase Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Right Column --}}
                @include('utilities.advert')
            </div>
        </div>
    </div>

    {{-- PIN Confirmation Modal --}}
    <div class="modal fade" id="pinModal" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-lg border-0">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-semibold" id="pinModalLabel">
                        <i class="bi bi-shield-lock-fill me-2"></i> Confirm Transaction
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center py-4">
                    <div class="mb-4 text-start bg-light p-3 rounded-3 border">
                        <h6 class="fw-bold border-bottom pb-2 mb-2">Transaction Summary</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Service:</span>
                            <span id="modal-service-name" class="fw-semibold text-primary">SME Data</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Plan:</span>
                            <span id="modal-plan-name" class="fw-semibold"></span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Recipient:</span>
                            <span id="modal-recipient" class="fw-semibold"></span>
                        </div>
                        <div class="d-flex justify-content-between mt-2 pt-2 border-top">
                            <span class="fw-bold">Total Amount:</span>
                            <span id="modal-amount" class="fw-bold text-danger"></span>
                        </div>
                    </div>

                    <p class="text-muted mb-3 small">
                        Please enter your <strong>5-digit transaction PIN</strong> to authorize this purchase.
                    </p>

                    <div class="d-flex justify-content-center">
                        <input 
                            type="password" 
                            name="pin" 
                            id="pinInput" 
                            class="form-control text-center fw-bold fs-3 py-3 border-2 border-primary rounded-pill shadow-sm w-50" 
                            maxlength="5" 
                            inputmode="numeric" 
                            placeholder="•••••"
                            required
                            style="letter-spacing: 10px; font-family: 'Courier New', monospace;"
                        >
                    </div>

                    <small id="pinError" class="text-danger d-none mt-3 d-block fw-semibold">
                        Incorrect PIN. Please try again.
                    </small>
                </div>

                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" id="confirmPinBtn" class="btn btn-primary px-4 rounded-pill fw-semibold">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="pinLoader" role="status" aria-hidden="true"></span>
                        <span id="confirmPinText">Confirm Purchase</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
<div class="row mt-3">

@push('scripts')
    <script>
    $(document).ready(function () {
        @if(session('success'))
            if(typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Success!', html: "{!! session('success') !!}" });
            }
        @endif
        
        @if(session('error'))
            if(typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Oops...', text: "{!! session('error') !!}" });
            } else {
                alert("{!! session('error') !!}");
            }
        @endif
        $("#service_id").change(function () {
            let service_id = $(this).val();
            if(!service_id) return;
            
            $.ajax({
                type: "get",
                url: "{{ route('sme.fetch.type') }}",
                data: { id: service_id },
                dataType: "json",
                success: function (response) {
                    var len = response.length;
                    $("#type").empty();
                    $("#type").append("<option value=''>Data Type</option>");

                    for (var i = 0; i < len; i++) {
                        var plan_type = response[i]["plan_type"];
                        $("#type").append("<option value='" + plan_type + "'>" + plan_type + "</option>");
                    }
                    
                    // Auto-select if only one type exists
                    if (len === 1) {
                        $("#type").val(response[0]["plan_type"]).trigger('change');
                    }

                    $("#plan").empty().append("<option value=''>Select Plan</option>");
                    $("#amountToPay").val("");
                },
                error: function (data) {
                    console.error("Error fetching data types", data);
                },
            });
        });

        $("#type").change(function () {
            let service_id = $("#service_id").val();
            let type = $(this).val();
            if(!service_id || !type) return;

            $.ajax({
                type: "get",
                url: "{{ route('sme.fetch.plan') }}",
                data: { id: service_id, type: type },
                dataType: "json",
                success: function (response) {
                    var len = response.length;
                    $("#plan").empty();
                    $("#plan").append("<option value=''>Data Plan</option>");

                    for (var i = 0; i < len; i++) {
                        var plan_text = response[i]["formatted_text"] || (response[i]["size"] + " " + response[i]["plan_type"] + " (₦" + response[i]["amount"] + ") " + response[i]["validity"]);
                        var id = response[i]["data_id"];
                        $("#plan").append("<option value='" + id + "'>" + plan_text + "</option>");
                    }
                    $("#amountToPay").val("");
                },
                error: function (data) {
                    console.error("Error fetching data plans", data);
                },
            });
        });

        $("#plan").change(function () {
            let plan_id = $(this).val();
            if(!plan_id) {
                $("#amountToPay").val("");
                return;
            }

            $.ajax({
                type: "get",
                url: "{{ route('sme.fetch.price') }}",
                data: { id: plan_id },
                dataType: "json",
                success: function (response) {
                    $("#amountToPay").val("₦ " + response);
                },
                error: function (data) {
                    console.error("Error fetching price", data);
                },
            });
        });

        // Purchase Click (Show Modal with Summary)
        $("#purchaseBtn").on('click', function() {
            const network = $("#service_id option:selected").text();
            const plan = $("#plan option:selected").text();
            const recipient = $("#mobileno").val();
            const amount = $("#amountToPay").val();

            if (!$("#service_id").val() || !$("#plan").val() || !recipient) {
                alert("Please fill all fields first.");
                return;
            }

            $("#modal-service-name").text(network + " Data");
            $("#modal-plan-name").text(plan);
            $("#modal-recipient").text(recipient);
            $("#modal-amount").text(amount);

            new bootstrap.Modal(document.getElementById('pinModal')).show();
        });

        // PIN Confirmation Logic
        $('#confirmPinBtn').on('click', function() {
            const confirmBtn = $(this);
            const loader = $('#pinLoader');
            const confirmText = $('#confirmPinText');
            const pinError = $('#pinError');
            const pin = $('#pinInput').val().trim();

            if (!pin) {
                pinError.text("Please enter your PIN.").removeClass('d-none');
                return;
            }

            confirmBtn.prop('disabled', true);
            loader.removeClass('d-none');
            confirmText.text("Verifying...");

            $.ajax({
                type: "POST",
                url: "{{ route('verify.pin') }}",
                data: { 
                    pin: pin,
                    _token: "{{ csrf_token() }}"
                },
                success: function(data) {
                    if (data.valid) {
                        $('#buySmeDataForm').submit();
                    } else {
                        pinError.text("Incorrect PIN. Please try again.").removeClass('d-none');
                        confirmBtn.prop('disabled', false);
                        loader.addClass('d-none');
                        confirmText.text("Confirm Purchase");
                    }
                },
                error: function() {
                    pinError.text("Network error. Please try again.").removeClass('d-none');
                    confirmBtn.prop('disabled', false);
                    loader.addClass('d-none');
                    confirmText.text("Confirm Purchase");
                }
            });
        });
    });
    </script>
@endpush

</x-app-layout>
