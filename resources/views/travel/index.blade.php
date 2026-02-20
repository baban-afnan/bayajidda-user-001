<x-app-layout>
    <title>Baya Jidda - Travel Application</title>
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">Travel Service</h3>
                        <p class="text-muted small mb-0">Apply for domestic and international travel services.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid mt-3">
            <div class="row">
                <!-- Travel Application Form -->
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 fw-bold text-white"><i class="ti ti-plane-departure me-2"></i>Travel Application Form</h5>
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

                            <form method="POST" action="{{ route('travel.store') }}" class="row g-4" enctype="multipart/form-data">
                                @csrf

                                <div class="col-md-6">
                                    <label for="service_field" class="form-label fw-bold">Select Travel Type <span class="text-danger">*</span></label>
                                    <select name="service_field" id="service_field" class="form-select border-primary-subtle" required>
                                        <option value="">-- Choose Travel Type --</option>
                                        @foreach($travelService->fields as $field)
                                            <option value="{{ $field->id }}" 
                                                    data-price="{{ $field->prices()->where('user_type', $role)->value('price') ?? $field->base_price }}"
                                                    data-description="{{ $field->description }}"
                                                    {{ old('service_field') == $field->id ? 'selected' : '' }}>
                                                {{ $field->field_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 text-end">
                                        <small class="text-muted fst-italic" id="field-description"></small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Visa Type</label>
                                    <select name="visa_type" class="form-select border-primary-subtle">
                                        <option value="">-- Select Visa Type --</option>
                                        <option value="Tourist" {{ old('visa_type') == 'Tourist' ? 'selected' : '' }}>Tourist</option>
                                        <option value="Business" {{ old('visa_type') == 'Business' ? 'selected' : '' }}>Business</option>
                                        <option value="Student" {{ old('visa_type') == 'Student' ? 'selected' : '' }}>Student</option>
                                        <option value="Work" {{ old('visa_type') == 'Work' ? 'selected' : '' }}>Work</option>
                                        <option value="Other" {{ old('visa_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="gender" id="male" value="Male" {{ old('gender', 'Male') == 'Male' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="male">Male</label>
                                        </div>
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="gender" id="female" value="Female" {{ old('gender') == 'Female' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="female">Female</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Applicant Class <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="applicant_class" id="adult" value="Adult" {{ old('applicant_class', 'Adult') == 'Adult' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="adult">Adult</label>
                                        </div>
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="applicant_class" id="child" value="Child" {{ old('applicant_class') == 'Child' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="child">Child</label>
                                        </div>
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="applicant_class" id="infant" value="Infant" {{ old('applicant_class') == 'Infant' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="infant">Infant</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12" id="passport_upload_wrapper" style="display: none;">
                                    <label class="form-label fw-bold">International Passport Data Page <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-camera"></i></span>
                                        <input class="form-control" name="passport_file" type="file" id="passport_file" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <small class="text-muted">Required for international travel applications. Max 5MB (PDF or Image).</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Trip Type <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="trip_type" id="one_way" value="one_way" {{ old('trip_type', 'one_way') == 'one_way' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="one_way">One Way</label>
                                        </div>
                                        <div class="form-check custom-radio">
                                            <input class="form-check-input" type="radio" name="trip_type" id="round_trip" value="round_trip" {{ old('trip_type') == 'round_trip' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="round_trip">Round Trip (Go and Comeback)</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Departure Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-calendar"></i></span>
                                        <input class="form-control" name="departure_date" type="date" required
                                               id="departure_date"
                                               value="{{ old('departure_date') }}" min="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-6" id="return_date_wrapper" style="{{ old('trip_type') == 'round_trip' ? '' : 'display: none;' }}">
                                    <label class="form-label fw-bold">Return Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-calendar"></i></span>
                                        <input class="form-control" name="return_date" type="date" 
                                               id="return_date"
                                               value="{{ old('return_date') }}" min="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">From <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-map-pin"></i></span>
                                        <input class="form-control" name="from_country" type="text" required
                                               placeholder="Departure Point (e.g. Nigeria)"
                                               value="{{ old('from_country') }}">
                                    </div>
                                </div>


                                <div class="col-md-6">
                                    <label class="form-label fw-bold">To <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-map-pin"></i></span>
                                        <input class="form-control" name="to_country" type="text" required
                                               placeholder="Destination"
                                               value="{{ old('to_country') }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-mail"></i></span>
                                        <input class="form-control" name="email" type="email" required
                                               placeholder="Email Address"
                                               value="{{ old('email', auth()->user()->email) }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="ti ti-phone"></i></span>
                                        <input class="form-control" name="phone_number" type="text" required
                                               placeholder="Phone Number"
                                               value="{{ old('phone_number', auth()->user()->phone) }}">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" rows="4" class="form-control border-primary-subtle" 
                                              placeholder="Provide additional details about your travel" 
                                              required>{{ old('description') }}</textarea>
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
                                        <i class="ti ti-send me-2"></i> Submit Application
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
                            <form class="row g-3 mb-4 bg-light p-3 rounded-3 border" method="GET" action="{{ route('travel.index') }}">
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
                                            <th>Route</th>
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
                                                    <small class="d-block text-muted">From: {{ $submission->from_country ?? $submission->country }} To: {{ $submission->to_country ?? 'N/A' }}</small>
                                                    <small class="d-block text-primary fw-bold">{{ str_replace('_', ' ', ucfirst($submission->trip_type ?? 'N/A')) }}</small>
                                                    <small class="d-block text-muted">Class: {{ $submission->applicant_class ?? 'N/A' }} | Gender: {{ $submission->gender ?? 'N/A' }}</small>
                                                    @if($submission->visa_type)
                                                        <small class="d-block text-muted">Visa: {{ $submission->visa_type }}</small>
                                                    @endif
                                                    
                                                    <small class="d-block text-muted mt-1">Dep: {{ $submission->departure_date ? $submission->departure_date->format('d M, Y') : 'N/A' }}</small>
                                                    @if($submission->trip_type === 'round_trip')
                                                        <small class="d-block text-muted">Ret: {{ $submission->return_date ? $submission->return_date->format('d M, Y') : 'N/A' }}</small>
                                                    @endif

                                                    @if($submission->passport_url)
                                                        <a href="{{ $submission->passport_url }}" target="_blank" class="btn btn-xs btn-outline-success mt-1 py-0 px-2" style="font-size: 0.65rem;">
                                                            <i class="ti ti-download me-1"></i>Passport
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
                                                    No travel applications found.
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
        
        /* Selection Visibility Fixes */
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



    <script src="{{ asset('assets/js/travel.js') }}"></script>
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
        });
    </script>
</x-app-layout>
