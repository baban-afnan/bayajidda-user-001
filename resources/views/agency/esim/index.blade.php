<x-app-layout>
    <title>Baya Jidda - ESIM Application</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">ESIM Card Services</h3>
                        <p class="text-muted small mb-0">Apply for your ESIM Card.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-3">
            <div class="row">
                <!-- ESIM Application Form -->
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 fw-bold text-white"><i class="ti ti-file-certificate me-2"></i>ESIM Form</h5>
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

                            <form method="POST" action="{{ route('esim.store') }}" class="row g-4" enctype="multipart/form-data">
                                @csrf

                                <div class="col-md-12">
                                    <label for="service_field" class="form-label fw-bold">Registration Option <span class="text-danger">*</span></label>
                                    <select name="service_field" id="service_field" class="form-select border-primary-subtle" required>
                                        <option value="">-- Choose Option --</option>
                                        @foreach($esimService->fields ?? [] as $field)
                                            <option value="{{ $field->id }}" 
                                                    data-price="{{ $field->prices()->where('user_type', $role)->value('price') ?? $field->base_price }}"
                                                    data-description="{{ $field->description }}"
                                                    {{ old('service_field') == $field->id ? 'selected' : '' }}>
                                                {{ ucfirst($field->field_name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 text-end">
                                        <small class="text-muted fst-italic" id="field-description"></small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" name="first_name" type="text" required
                                               placeholder="First Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Middle Name</label>
                                    <div class="input-group">
                                        <input class="form-control" name="middle_name" type="text"
                                               placeholder="Middle Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" name="last_name" type="text" required
                                               placeholder="Last Name">
                                    </div>
                                </div>

                                 <div class="col-md-4">
                                    <label class="form-label fw-bold">NIN <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input class="form-control" name="nin" type="text" required
                                               placeholder="NIN No" maxlength="11">
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-mail"></i></span>
                                        <input class="form-control" name="email" type="email" required
                                               placeholder="Email Address">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-phone"></i></span>
                                        <input class="form-control" name="phone_number" type="text" required
                                               placeholder="Phone Number">
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                    <textarea name="address" rows="3" class="form-control border-primary-subtle" 
                                              placeholder="Full living address" required>{{ old('address') }}</textarea>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Photo (White Background) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-photo"></i></span>
                                        <input class="form-control" name="photo_file" type="file" required id="photo_file" accept=".jpg,.jpeg,.png">
                                    </div>
                                    <small class="text-muted">Max 5MB (Image only).</small>
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
                                        <i class="ti ti-send me-2"></i> Submit ESIM Application
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Application History -->
                <div class="col-xl-6">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="ti ti-history me-2 text-primary"></i> Application History
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form class="row g-3 mb-4 bg-light p-3 rounded-3 border" method="GET" action="{{ route('esim.index') }}">
                                <div class="col-md-6">
                                    <input class="form-control border-0 shadow-sm" name="search" type="text" placeholder="Search email or phone..." value="{{ request('search') }}">
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
                                            <th>Details</th>
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
                                                    <small class="d-block text-primary fw-bold">{{ $submission->service_field_name }}</small>
                                                    <small class="d-block text-muted">{{ $submission->first_name }} {{ $submission->last_name }}</small>
                                                    <small class="d-block text-muted">{{ $submission->phone_number }}</small>
                                                    @if($submission->passport_url)
                                                        <a href="{{ $submission->passport_url }}" target="_blank" class="btn btn-xs btn-outline-info mt-1 py-0 px-2" style="font-size: 0.65rem;">
                                                            <i class="ti ti-photo me-1"></i>Photo
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
                                                            data-comment="{{ $submission->comment ?? 'Your application is being reviewed.' }}">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="ti ti-folder-off fs-1 d-block mb-3"></i>
                                                    No applications found.
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

        {{-- Comment Modal --}}
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
            box-shadow: 0 0 0 0.25rem rgba(0, 47, 186, 0.25);
        }
        .form-check-label {
            cursor: pointer;
            transition: color 0.2s ease;
        }
        .form-check-input:checked + .form-check-label {
            color: #002fba;
            font-weight: 600;
        }
        .form-select:focus, .form-control:focus {
            border-color: #002fba;
            box-shadow: 0 0 0 0.25rem rgba(0, 47, 186, 0.25);
        }
        .custom-radio {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }
        .custom-radio:hover {
            background-color: #f8f9fa;
            border-color: #002fba;
        }
        .custom-radio .form-check-input:checked ~ .form-check-label {
            color: #002fba;
        }
        .custom-radio:has(.form-check-input:checked) {
            border-color: #002fba;
            background-color: rgba(0, 47, 186, 0.05);
            box-shadow: 0 2px 4px rgba(0, 47, 186, 0.1);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if (session('status') && session('message'))
                Swal.fire({
                    icon: "{{ session('status') === 'success' ? 'success' : 'error' }}",
                    title: "{{ session('status') === 'success' ? 'Great!' : 'Oops!' }}",
                    text: "{{ session('message') }}",
                    confirmButtonColor: '#002fba',
                });
            @endif

            // Dynamic Price Calculation
            const serviceField = document.getElementById('service_field');
            const fieldPriceDiv = document.getElementById('field-price');
            const totalAmountDiv = document.getElementById('total-amount');

            if(serviceField) {
                serviceField.addEventListener('change', function() {
                    const selected = this.options[this.selectedIndex];
                    const price = parseFloat(selected.getAttribute('data-price')) || 0;
                    const formatted = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(price);
                    fieldPriceDiv.textContent = formatted;
                    totalAmountDiv.textContent = formatted;
                });
            }
        });
    </script>
</x-app-layout>
