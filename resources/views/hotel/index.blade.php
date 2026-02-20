<x-app-layout>
    <title>Baya Jidda - Hotel Reservation</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">Hotel Service</h3>
                        <p class="text-muted small mb-0">Book hotels in Nigeria and international destinations.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-3">
            <div class="row">
                <!-- Hotel Booking Form -->
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 fw-bold text-white"><i class="ti ti-building-hospital me-2"></i>Hotel Reservation Form</h5>
                        </div>

                        <div class="card-body p-4">
                            @if (session('message'))
                                <div class="alert alert-{{ session('status') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show border-0 shadow-sm mb-4">
                                    <i class="ti ti-{{ session('status') === 'success' ? 'check-circle' : 'exclamation-circle' }} me-2"></i>
                                    {{ session('message') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4">
                                    <ul class="mb-0 small">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('hotel.store') }}" class="row g-4" enctype="multipart/form-data">
                                @csrf

                                <div class="col-md-6">
                                    <label for="service_field" class="form-label fw-bold">Reservation Type <span class="text-danger">*</span></label>
                                    <select name="service_field" id="service_field" class="form-select border-primary-subtle" required>
                                        @foreach($hotelService->fields as $field)
                                            <option value="{{ $field->id }}" 
                                                    data-price="{{ $field->prices()->where('user_type', $role)->value('price') ?? $field->base_price }}"
                                                    {{ old('service_field') == $field->id ? 'selected' : '' }}>
                                                {{ $field->field_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Country <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-world"></i></span>
                                        <input class="form-control" name="country" type="text" required
                                               placeholder="e.g. Nigeria"
                                               value="{{ old('country') }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Hotel Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-building"></i></span>
                                        <input class="form-control" name="hotel_name" type="text" required
                                               placeholder="Name of the hotel"
                                               value="{{ old('hotel_name') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Check-in <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-calendar-event"></i></span>
                                        <input class="form-control" name="check_in" type="date" required
                                               value="{{ old('check_in') }}" min="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Check-out <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-calendar-check"></i></span>
                                        <input class="form-control" name="check_out" type="date" required
                                               value="{{ old('check_out') }}" min="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">State <span class="text-danger">*</span></label>
                                    <input class="form-control" name="state" type="text" required
                                           placeholder="State" value="{{ old('state') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">LGA <span class="text-danger">*</span></label>
                                    <input class="form-control" name="lga" type="text" required
                                           placeholder="Local Government" value="{{ old('lga') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Full Address <span class="text-danger">*</span></label>
                                    <textarea name="address" rows="2" class="form-control border-primary-subtle" 
                                              placeholder="Street address where you want to stay" 
                                              required>{{ old('address') }}</textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-mail"></i></span>
                                        <input class="form-control" name="email" type="email" required
                                               value="{{ old('email', auth()->user()->email) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-phone"></i></span>
                                        <input class="form-control" name="phone_number" type="text" required
                                               value="{{ old('phone_number', auth()->user()->phone) }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">International Passport / National ID <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-id"></i></span>
                                        <input class="form-control" name="passport_file" type="file" required accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <small class="text-muted">Upload a clear copy of your identity document. Max 5MB (PDF or Image).</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Notes (Hotel Option) <span class="text-muted fw-normal">(Optional)</span></label>
                                    <textarea name="notes" rows="2" class="form-control border-primary-subtle" 
                                              placeholder="Any specific requests or preferences?">{{ old('notes') }}</textarea>
                                </div>

                                <div class="col-md-6 text-center">
                                    <label class="form-label fw-bold">Service Fee</label>
                                    <div class="alert alert-secondary py-2 border-0 shadow-sm mb-0 text-center">
                                        <span class="h5 fw-bold mb-0 text-primary" id="field-price">₦0.00</span>
                                    </div>
                                </div>

                                <div class="col-md-6 text-center">
                                    <label class="form-label fw-bold">Total Payable</label>
                                    <div class="alert alert-soft-warning py-2 border-0 shadow-sm mb-0 text-center">
                                        <span class="h5 fw-bold mb-0 text-dark" id="total-amount">₦0.00</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted fw-semibold">Your Wallet Balance:</span>
                                        <span class="text-success fw-bold">₦{{ number_format($wallet->balance ?? 0, 2) }}</span>
                                    </div>
                                </div>

                                <div class="col-12 d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg shadow-sm hover-up">
                                        <i class="ti ti-send me-2"></i> Submit Reservation
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reservation History -->
                <div class="col-xl-6">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="ti ti-history me-2 text-primary"></i> Reservation History
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 mb-4 bg-light p-3 rounded-3 border" method="GET" action="{{ route('hotel.index') }}">
                                <div class="col-md-6">
                                    <input class="form-control border-0 shadow-sm" name="search" type="text" placeholder="Search reference, email or phone..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4">
                                    <select class="form-select border-0 shadow-sm" name="status">
                                        <option value="">All Statuses</option>
                                        @foreach(['pending', 'processing', 'successful', 'failed'] as $status)
                                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                                {{ ucfirst($status) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100 shadow-sm" type="submit">
                                        <i class="ti ti-filter"></i>
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>#</th>
                                            <th>Reference</th>
                                            <th>Hotel Details</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($submissions as $submission)
                                            <tr>
                                                <td class="fw-bold text-muted">{{ $loop->iteration + $submissions->firstItem() - 1 }}</td>
                                                <td><span class="text-primary fw-medium">{{ $submission->reference }}</span></td>
                                                <td>
                                                    <small class="d-block text-dark fw-bold">{{ $submission->company_name }}</small>
                                                    <small class="d-block text-muted">{{ $submission->country }} | {{ $submission->state }}, {{ $submission->lga }}</small>
                                                    <small class="d-block text-primary mt-1">In: {{ $submission->departure_date ? $submission->departure_date->format('d M, Y') : 'N/A' }} | Out: {{ $submission->return_date ? $submission->return_date->format('d M, Y') : 'N/A' }}</small>
                                                    
                                                    @if($submission->passport_url)
                                                        <a href="{{ $submission->passport_url }}" target="_blank" class="btn btn-xs btn-outline-success mt-1 py-0 px-2" style="font-size: 0.65rem;">
                                                            <i class="ti ti-download me-1"></i>ID Document
                                                        </a>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="badge rounded-pill bg-{{ match($submission->status) {
                                                        'successful' => 'success',
                                                        'processing' => 'primary',
                                                        'failed' => 'danger',
                                                        default => 'warning'
                                                    } }}">{{ ucfirst($submission->status) }}</span>
                                                </td>
                                                <td>
                                                    <button type="button"
                                                            class="btn btn-sm btn-icon btn-outline-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#commentModal"
                                                            data-comment="{{ $submission->comment ?? 'Your reservation is being processed.' }}">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="ti ti-folder-off fs-1 d-block mb-3"></i>
                                                    No reservations found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 d-flex justify-content-center">
                                {{ $submissions->withQueryString()->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('pages.comment')
    </div>

    <style>
        .hover-up:hover { transform: translateY(-3px); transition: all 0.3s ease; }
        .alert-soft-warning { background-color: #fff3cd; color: #664d03; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
        .table thead th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        
        .form-check-input:checked {
            background-color: #002fba !important;
            border-color: #002fba !important;
        }
        .form-select:focus, .form-control:focus {
            border-color: #002fba;
            box-shadow: 0 0 0 0.25rem rgba(0, 47, 186, 0.25);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const serviceFieldSelect = document.getElementById('service_field');
            const fieldPriceSpan = document.getElementById('field-price');
            const totalAmountSpan = document.getElementById('total-amount');

            function updatePrice() {
                const selectedOption = serviceFieldSelect.options[serviceFieldSelect.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                
                const formattedPrice = new Intl.NumberFormat('en-NG', {
                    style: 'currency',
                    currency: 'NGN'
                }).format(price);

                fieldPriceSpan.textContent = formattedPrice;
                totalAmountSpan.textContent = formattedPrice;
            }

            if (serviceFieldSelect) {
                serviceFieldSelect.addEventListener('change', updatePrice);
                updatePrice();
            }

            @if (session('status') && session('message'))
                Swal.fire({
                    icon: "{{ session('status') === 'success' ? 'success' : 'error' }}",
                    title: "{{ session('status') === 'success' ? 'Great!' : 'Oops!' }}",
                    text: "{{ session('message') }}",
                    confirmButtonColor: '#002fba',
                });
            @endif
        });
    </script>
</x-app-layout>
