<x-app-layout>
    <title>Bayajidda Global - CAC Submission Details</title>
    
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-4">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3 class="fw-bold text-primary">CAC Submission Details</h3>
                        <p class="text-muted small mb-0">Reference: <span class="fw-bold text-dark">{{ $submission->reference }}</span></p>
                    </div>
                    <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                        <a href="{{ route('cac.index') }}" class="btn btn-outline-primary shadow-sm">
                            <i class="bi bi-arrow-left me-2"></i> Back to Registrations
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Details Card -->
                <div class="col-xl-8">
                    <!-- Status Card -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden">
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center p-4 bg-{{ match($submission->status) {
                                'successful' => 'success-subtle',
                                'processing', 'in-progress' => 'info-subtle',
                                'pending' => 'warning-subtle',
                                'query' => 'secondary-subtle',
                                'rejected', 'failed' => 'danger-subtle',
                                default => 'light'
                            } }}">
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 fw-bold">Current Status: 
                                        <span class="text-{{ match($submission->status) {
                                            'successful' => 'success',
                                            'processing', 'in-progress' => 'info',
                                            'pending' => 'warning',
                                            'query' => 'secondary',
                                            'rejected', 'failed' => 'danger',
                                            default => 'dark'
                                        } }}">{{ ucfirst($submission->status) }}</span>
                                    </h5>
                                    <p class="mb-0 text-muted small">Submitted on {{ $submission->submission_date->format('F d, Y \a\t h:i A') }}</p>
                                </div>
                                <div class="ms-3">
                                    <i class="bi bi-{{ match($submission->status) {
                                        'successful' => 'check-circle-fill text-success',
                                        'processing', 'in-progress' => 'gear-wide-connected text-info',
                                        'pending' => 'clock-fill text-warning',
                                        'query' => 'question-circle-fill text-secondary',
                                        'rejected', 'failed' => 'x-circle-fill text-danger',
                                        default => 'info-circle'
                                    } }} fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-building me-2"></i>Business Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="text-muted small fw-bold d-block mb-1">PROPOSED BUSINESS NAME</label>
                                    <h6 class="fw-bold mb-0 text-dark">{{ $submission->company_name }}</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small fw-bold d-block mb-1">BUSINESS TYPE</label>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3">{{ str_replace('_', ' ', strtoupper($submission->company_type)) }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="text-muted small fw-bold d-block mb-1">NATURE OF BUSINESS</label>
                                    <p class="text-dark mb-0 bg-light p-3 rounded-3 border">{{ $submission->description ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-12">
                                    <label class="text-muted small fw-bold d-block mb-1">BUSINESS ADDRESS</label>
                                    <div class="d-flex align-items-start bg-light p-3 rounded-3 border">
                                        <i class="bi bi-geo-alt-fill text-primary me-3 mt-1 fs-5"></i>
                                        <div>
                                            <h6 class="mb-1 text-dark">{{ $submission->business_house_number }} {{ $submission->business_street }}</h6>
                                            <p class="mb-1 text-muted">{{ $submission->business_city }}, {{ $submission->business_lga }} LGA, {{ $submission->business_state }} State</p>
                                            @if($submission->business_description)
                                                <small class="text-info"><i class="bi bi-info-circle me-1"></i> {{ $submission->business_description }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Director 1 Information -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-fill me-2"></i>Director 1 (Applicant)</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">FULL NAME</label>
                                    <span class="text-dark fw-bold">{{ $submission->last_name }}, {{ $submission->first_name }} {{ $submission->middle_name }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">PHONE NUMBER</label>
                                    <span class="text-dark">{{ $submission->phone_number }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">EMAIL ADDRESS</label>
                                    <span class="text-dark">{{ $submission->email }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">DATE OF BIRTH</label>
                                    <span class="text-dark">{{ $submission->dob ? $submission->dob->format('d M, Y') : 'N/A' }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">GENDER</label>
                                    <span class="text-dark">{{ $submission->gender }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="text-muted small fw-bold d-block mb-1">RESIDENTIAL ADDRESS</label>
                                    <div class="bg-light p-3 rounded-3 border">
                                        <p class="mb-0 text-dark">{{ $submission->house_number }} {{ $submission->street_name }}, {{ $submission->city }}, {{ $submission->lga }} LGA, {{ $submission->state }} State</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Director 2 Information -->
                    @if($submission->director2_first_name)
                    <div class="card shadow-sm border-0 rounded-3 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person-plus-fill me-2"></i>Director 2</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">FULL NAME</label>
                                    <span class="text-dark fw-bold">{{ $submission->director2_surname }}, {{ $submission->director2_first_name }} {{ $submission->director2_middle_name }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">PHONE NUMBER</label>
                                    <span class="text-dark">{{ $submission->director2_phone }}</span>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold d-block mb-1">EMAIL ADDRESS</label>
                                    <span class="text-dark">{{ $submission->director2_email }}</span>
                                </div>
                                <div class="col-md-12">
                                    <label class="text-muted small fw-bold d-block mb-1">RESIDENTIAL ADDRESS</label>
                                    <div class="bg-light p-3 rounded-3 border">
                                        <p class="mb-0 text-dark">{{ $submission->director2_address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar Details -->
                <div class="col-xl-4">
                    <!-- Documents Card -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0 fw-bold text-white"><i class="bi bi-file-earmark-text me-2"></i>Documents</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Director 1 Uploads</h6>
                            <div class="list-group list-group-flush mb-4">
                                <a href="{{ route('cac.download', ['reference' => $submission->reference, 'fileType' => 'nin']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-secondary-subtle p-2 rounded me-3">
                                        <i class="bi bi-card-text text-secondary fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">NIN Slip</p>
                                        <small class="text-muted">Click to download/view</small>
                                    </div>
                                    <i class="bi bi-download text-primary"></i>
                                </a>
                                <a href="{{ route('cac.download', ['reference' => $submission->reference, 'fileType' => 'signature']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-secondary-subtle p-2 rounded me-3">
                                        <i class="bi bi-pen text-secondary fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">Signature</p>
                                        <small class="text-muted">Click to download/view</small>
                                    </div>
                                    <i class="bi bi-download text-primary"></i>
                                </a>
                                <a href="{{ route('cac.download', ['reference' => $submission->reference, 'fileType' => 'passport']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-secondary-subtle p-2 rounded me-3">
                                        <i class="bi bi-person-badge text-secondary fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">Passport Photograph</p>
                                        <small class="text-muted">Click to download/view</small>
                                    </div>
                                    <i class="bi bi-download text-primary"></i>
                                </a>
                            </div>

                            @if($submission->director2_first_name)
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Director 2 Uploads</h6>
                            <div class="list-group list-group-flush mb-4">
                                <a href="{{ route('cac.download', ['reference' => $submission->reference, 'fileType' => 'director2_nin']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-info-subtle p-2 rounded me-3">
                                        <i class="bi bi-card-text text-info fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">Director 2 NIN</p>
                                    </div>
                                    <i class="bi bi-download text-primary"></i>
                                </a>
                                <a href="{{ route('cac.download', ['reference' => $submission->reference, 'fileType' => 'director2_signature']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-info-subtle p-2 rounded me-3">
                                        <i class="bi bi-pen text-info fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">Director 2 Signature</p>
                                    </div>
                                    <i class="bi bi-download text-primary"></i>
                                </a>
                                <a href="{{ route('cac.download', ['reference' => $submission->reference, 'fileType' => 'director2_passport']) }}" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-info-subtle p-2 rounded me-3">
                                        <i class="bi bi-person-badge text-info fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">Director 2 Passport</p>
                                    </div>
                                    <i class="bi bi-download text-primary"></i>
                                </a>
                            </div>
                            @endif

                            @if($submission->cac_file || $submission->memart_file || $submission->status_report_file || $submission->tin_file)
                            <h6 class="fw-bold mb-3 border-bottom pb-2 text-success">Completed Documents</h6>
                            <div class="list-group list-group-flush">
                                @if($submission->cac_file)
                                <a href="{{ asset('storage/'.$submission->cac_file) }}" target="_blank" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-success-subtle p-2 rounded me-3">
                                        <i class="bi bi-file-earmark-check text-success fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">CAC Certificate</p>
                                    </div>
                                    <i class="bi bi-eye text-success"></i>
                                </a>
                                @endif
                                @if($submission->memart_file)
                                <a href="{{ asset('storage/'.$submission->memart_file) }}" target="_blank" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-success-subtle p-2 rounded me-3">
                                        <i class="bi bi-file-earmark-ruled text-success fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">MEMART</p>
                                    </div>
                                    <i class="bi bi-eye text-success"></i>
                                </a>
                                @endif
                                @if($submission->status_report_file)
                                <a href="{{ asset('storage/'.$submission->status_report_file) }}" target="_blank" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-success-subtle p-2 rounded me-3">
                                        <i class="bi bi-file-earmark-medical text-success fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">Status Report</p>
                                    </div>
                                    <i class="bi bi-eye text-success"></i>
                                </a>
                                @endif
                                @if($submission->tin_file)
                                <a href="{{ asset('storage/'.$submission->tin_file) }}" target="_blank" class="list-group-item list-group-item-action border-0 px-0 d-flex align-items-center">
                                    <div class="bg-success-subtle p-2 rounded me-3">
                                        <i class="bi bi-file-lock text-success fs-5"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-semibold small text-dark">TIN Certificate</p>
                                    </div>
                                    <i class="bi bi-eye text-success"></i>
                                </a>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="card shadow-sm border-0 rounded-3 mb-4 overflow-hidden">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="mb-0 fw-bold text-white"><i class="bi bi-credit-card me-2"></i>Payment Info</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Service Fee:</span>
                                <span class="fw-bold text-dark">₦{{ number_format($submission->amount, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Payment Type:</span>
                                <span class="text-dark">Wallet Debit</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Transaction ID:</span>
                                <span class="text-dark">TRX-{{ $submission->transaction_id }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
