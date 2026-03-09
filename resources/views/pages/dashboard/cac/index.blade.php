<x-app-layout>
    <title>Bayajidda Global - {{ $title ?? 'CAC Registration' }}</title>
    
    <div class="page-body">
        <div class="container-fluid">
            <div class="page-title mb-3">
                <div class="row">
                    <div class="col-sm-6 col-12">
                        <h3 class="fw-bold text-primary">CAC Registration Service</h3>
                        <p class="text-muted small mb-0">Register your business with the Corporate Affairs Commission.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <!-- CAC Registration Form -->
            <div class="col-xl-8 mb-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-white"><i class="bi bi-building me-2 text-white"></i>CAC New Registration</h5>
                        <span class="badge bg-light text-primary fw-semibold">Bayajidda Global</span>
                    </div>

                    <div class="card-body">
                        <div class="text-center mb-3">
                            <p class="text-muted small mb-0">
                                Fill in the details below to register your business.
                            </p>
                        </div>

                        {{-- Alerts --}}
                        @if (session('status'))
                            <div class="alert alert-{{ session('status') === 'success' ? 'success' : (session('status') === 'error' ? 'danger' : session('status')) }} alert-dismissible fade show">
                                {{ session('message') ?? session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @elseif (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @elseif (session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <ul class="mb-0 small text-start">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        {{-- CAC Form Wizard --}}
                        <form method="POST" action="{{ route('cac.store') }}" enctype="multipart/form-data" id="cacForm">
                            @csrf
                            
                            {{-- Step 1: Service Type & Business Details --}}
                            <div class="wizard-step" id="step-1">
                                <!-- Service Type Selection -->
                                <div class="mb-4 p-3 bg-light rounded border">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold">Registration Type <span class="text-danger">*</span></label>
                                            <select class="form-select" name="service_field_id" id="service_field" required>
                                                <option value="">-- Select Registration Type --</option>
                                                @foreach ($fields as $field)
                                                    @php
                                                        $price = $field->prices
                                                            ->where('user_type', auth()->user()->role)
                                                            ->first()?->price ?? $field->base_price;
                                                    @endphp
                                                    <option value="{{ $field->id }}"
                                                            data-price="{{ $price }}"
                                                            data-name="{{ $field->field_name }}"
                                                            data-description="{{ $field->description }}"
                                                            {{ old('service_field_id') == $field->id ? 'selected' : '' }}>
                                                        {{ $field->field_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 text-center mt-3 mt-md-0">
                                            <div class="p-2 border rounded bg-white">
                                                <small class="text-muted d-block">Service Fee</small>
                                                <h4 class="fw-bold text-primary mb-0" id="price-display">₦0.00</h4>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">Wallet Balance:</small>
                                                <strong class="text-success">₦{{ number_format($wallet->balance, 2) }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="business-details-section" class="{{ old('service_field_id') ? '' : 'd-none' }}">
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Business Details</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">Business Type <span class="text-danger">*</span></label>
                                            <select name="business_type" class="form-select @error('business_type') is-invalid @enderror" required>
                                                <option value="" disabled selected>Select Business Type</option>
                                                <option value="incorporated_trustee" {{ old('business_type') == 'incorporated_trustee' ? 'selected' : '' }}>INCORPORATED TRUSTEE LIMITED BY GUARANTEE</option>
                                                <option value="partnership" {{ old('business_type') == 'partnership' ? 'selected' : '' }}>PARTNERSHIP</option>
                                                <option value="private_limited" {{ old('business_type') == 'private_limited' ? 'selected' : '' }}>PRIVATE LIMITED COMPANY</option>
                                                <option value="private_unlimited" {{ old('business_type') == 'private_unlimited' ? 'selected' : '' }}>PRIVATE UNLIMITED COMPANY</option>
                                                <option value="public_limited" {{ old('business_type') == 'public_limited' ? 'selected' : '' }}>PUBLIC LIMITED COMPANY</option>
                                                <option value="sole_proprietorship" {{ old('business_type') == 'sole_proprietorship' ? 'selected' : '' }}>SOLE PROPRIETORSHIP</option>
                                                <option value="business_name" {{ old('business_type') == 'business_name' ? 'selected' : '' }}>BUSINESS NAME</option>
                                                <option value="llp" {{ old('business_type') == 'llp' ? 'selected' : '' }}>LIMITED LIABILITY PARTNERSHIP</option>
                                                <option value="limited_partnership" {{ old('business_type') == 'limited_partnership' ? 'selected' : '' }}>LIMITED PARTNERSHIP</option>
                                            </select>
                                            @error('business_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Nature of Business <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('nature_of_business') is-invalid @enderror" 
                                                   name="nature_of_business" value="{{ old('nature_of_business') }}" required>
                                            @error('nature_of_business')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Proposed Business Name 1 <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('business_name_1') is-invalid @enderror" 
                                                   name="business_name_1" value="{{ old('business_name_1') }}" required>
                                            @error('business_name_1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Proposed Business Name 2</label>
                                            <input type="text" class="form-control @error('business_name_2') is-invalid @enderror" 
                                                   name="business_name_2" value="{{ old('business_name_2') }}">
                                            @error('business_name_2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 2: Personal Information (Director 1) --}}
                            <div class="wizard-step d-none" id="step-2">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Personal Information (Director 1)</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">Surname <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('surname') is-invalid @enderror" 
                                               name="surname" value="{{ old('surname') }}" required>
                                        @error('surname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                               name="first_name" value="{{ old('first_name') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Other Name</label>
                                        <input type="text" class="form-control @error('other_name') is-invalid @enderror" 
                                               name="other_name" value="{{ old('other_name') }}">
                                        @error('other_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                               name="date_of_birth" value="{{ old('date_of_birth') }}" required>
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                                        <select class="form-select @error('gender') is-invalid @enderror" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone_number') is-invalid @enderror" 
                                               name="phone_number" value="{{ old('phone_number') }}" required>
                                        @error('phone_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Functional Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Residential Address (Director 1)</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                               name="state" value="{{ old('state') }}" required>
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">LGA <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('lga') is-invalid @enderror" 
                                               name="lga" value="{{ old('lga') }}" required>
                                        @error('lga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City/Town/Village <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                               name="city" value="{{ old('city') }}" required>
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">House Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('house_number') is-invalid @enderror" 
                                               name="house_number" value="{{ old('house_number') }}" required>
                                        @error('house_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Street Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('street_name') is-invalid @enderror" 
                                               name="street_name" value="{{ old('street_name') }}" required>
                                        @error('street_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Description of House Address</label>
                                        <textarea class="form-control @error('res_description') is-invalid @enderror" 
                                                  name="res_description" rows="2">{{ old('res_description') }}</textarea>
                                        @error('res_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left me-1"></i> Previous</button>
                                    <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- Step 3: Second Director (Optional) --}}
                            <div class="wizard-step d-none" id="step-3">
                                <div class="mb-4">
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i> 
                                        You can add a second director if applicable, or skip this step.
                                    </div>
                                    
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Personal Information (Director 2)</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label">Surname</label>
                                            <input type="text" class="form-control @error('director2_surname') is-invalid @enderror" 
                                                   name="director2_surname" value="{{ old('director2_surname') }}">
                                            @error('director2_surname')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control @error('director2_first_name') is-invalid @enderror" 
                                                   name="director2_first_name" value="{{ old('director2_first_name') }}">
                                            @error('director2_first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Other Name</label>
                                            <input type="text" class="form-control @error('director2_other_name') is-invalid @enderror" 
                                                   name="director2_other_name" value="{{ old('director2_other_name') }}">
                                            @error('director2_other_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control @error('director2_date_of_birth') is-invalid @enderror" 
                                                   name="director2_date_of_birth" value="{{ old('director2_date_of_birth') }}">
                                            @error('director2_date_of_birth')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select @error('director2_gender') is-invalid @enderror" name="director2_gender">
                                                <option value="">Select Gender</option>
                                                <option value="Male" {{ old('director2_gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('director2_gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                                <option value="Other" {{ old('director2_gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('director2_gender')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Phone Number</label>
                                            <input type="tel" class="form-control @error('director2_phone_number') is-invalid @enderror" 
                                                   name="director2_phone_number" value="{{ old('director2_phone_number') }}">
                                            @error('director2_phone_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Email Address</label>
                                            <input type="email" class="form-control @error('director2_email') is-invalid @enderror" 
                                                   name="director2_email" value="{{ old('director2_email') }}">
                                            @error('director2_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Residential Address (Director 2)</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label">State</label>
                                            <input type="text" class="form-control @error('director2_res_state') is-invalid @enderror" 
                                                   name="director2_res_state" value="{{ old('director2_res_state') }}">
                                            @error('director2_res_state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">LGA</label>
                                            <input type="text" class="form-control @error('director2_res_lga') is-invalid @enderror" 
                                                   name="director2_res_lga" value="{{ old('director2_res_lga') }}">
                                            @error('director2_res_lga')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">City/Town/Village</label>
                                            <input type="text" class="form-control @error('director2_res_city') is-invalid @enderror" 
                                                   name="director2_res_city" value="{{ old('director2_res_city') }}">
                                            @error('director2_res_city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">House Number</label>
                                            <input type="text" class="form-control @error('director2_res_house_no') is-invalid @enderror" 
                                                   name="director2_res_house_no" value="{{ old('director2_res_house_no') }}">
                                            @error('director2_res_house_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Street Name</label>
                                            <input type="text" class="form-control @error('director2_res_street') is-invalid @enderror" 
                                                   name="director2_res_street" value="{{ old('director2_res_street') }}">
                                            @error('director2_res_street')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description of Address</label>
                                            <textarea class="form-control @error('director2_res_description') is-invalid @enderror" 
                                                      name="director2_res_description" rows="2">{{ old('director2_res_description') }}</textarea>
                                            @error('director2_res_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Director 2 Uploads -->
                                    <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Supporting Documents (Director 2)</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label">NIN Slip (Director 2)</label>
                                            <input type="file" class="form-control @error('director2_nin_upload') is-invalid @enderror" 
                                                   name="director2_nin_upload" accept=".jpg,.jpeg,.png,.pdf">
                                            @error('director2_nin_upload')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Signature (Director 2)</label>
                                            <input type="file" class="form-control @error('director2_signature_upload') is-invalid @enderror" 
                                                   name="director2_signature_upload" accept=".jpg,.jpeg,.png,.pdf">
                                            @error('director2_signature_upload')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Passport (Director 2)</label>
                                            <input type="file" class="form-control @error('director2_passport_upload') is-invalid @enderror" 
                                                   name="director2_passport_upload" accept=".jpg,.jpeg,.png,.pdf">
                                            @error('director2_passport_upload')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left me-1"></i> Previous</button>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary me-2 skip-step">Skip <i class="bi bi-skip-forward ms-1"></i></button>
                                        <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>

                            {{-- Step 4: Business Address --}}
                            <div class="wizard-step d-none" id="step-4">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Business Address</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">State <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('bus_state') is-invalid @enderror" 
                                               name="bus_state" value="{{ old('bus_state') }}" required>
                                        @error('bus_state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">LGA <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('bus_lga') is-invalid @enderror" 
                                               name="bus_lga" value="{{ old('bus_lga') }}" required>
                                        @error('bus_lga')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">City/Town/Village <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('bus_city') is-invalid @enderror" 
                                               name="bus_city" value="{{ old('bus_city') }}" required>
                                        @error('bus_city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">House Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('bus_house_no') is-invalid @enderror" 
                                               name="bus_house_no" value="{{ old('bus_house_no') }}" required>
                                        @error('bus_house_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Street Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('bus_street') is-invalid @enderror" 
                                               name="bus_street" value="{{ old('bus_street') }}" required>
                                        @error('bus_street')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Description of Business Address</label>
                                        <textarea class="form-control @error('bus_description') is-invalid @enderror" 
                                                  name="bus_description" rows="2">{{ old('bus_description') }}</textarea>
                                        @error('bus_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left me-1"></i> Previous</button>
                                    <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right ms-1"></i></button>
                                </div>
                            </div>

                            {{-- Step 5: Uploads & Submit --}}
                            <div class="wizard-step d-none" id="step-5">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Supporting Documents (Director 1)</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label">NIN Slip (Director 1) <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('nin_upload') is-invalid @enderror" 
                                               name="nin_upload" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <small class="text-muted">Max 2MB (JPG, PNG, PDF)</small>
                                        @error('nin_upload')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Signature (Director 1) <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('signature_upload') is-invalid @enderror" 
                                               name="signature_upload" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <small class="text-muted">Max 2MB (JPG, PNG, PDF)</small>
                                        @error('signature_upload')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Passport (Director 1) <span class="text-danger">*</span></label>
                                        <input type="file" class="form-control @error('passport_upload') is-invalid @enderror" 
                                               name="passport_upload" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <small class="text-muted">Max 2MB (JPG, PNG, PDF)</small>
                                        @error('passport_upload')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Terms -->
                                <div class="col-md-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" id="termsCheckbox" type="checkbox" required>
                                        <label class="form-check-label fw-semibold small" for="termsCheckbox">
                                            I confirm that the provided information is accurate and agree to the service terms.
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left me-1"></i> Previous</button>
                                    <button type="submit" class="btn btn-primary btn-lg fw-semibold" id="submitBtn">
                                        <i class="bi bi-send-fill me-2"></i> Submit Registration
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Submission History -->
            <div class="col-xl-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-white">
                            <i class="bi bi-clock-history me-2"></i> History
                        </h5>
                    </div>

                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" class="mb-3">
                            <div class="input-group">
                                <input class="form-control" name="search" type="text" placeholder="Search Reference..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ref</th>
                                        <th>Business</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($submissions as $submission)
                                        <tr>
                                            <td class="small">{{ substr($submission->reference, -8) }}</td>
                                            <td class="small">{{ Str::limit($submission->company_name ?? 'N/A', 15) }}</td>
                                            <td>
                                                <span class="badge bg-{{ match($submission->status) {
                                                    'successful' => 'success',
                                                    'processing', 'in-progress' => 'info',
                                                    'pending' => 'warning',
                                                    'query'      => 'secondary',
                                                    'rejected', 'failed'   => 'danger',
                                                    default      => 'secondary'
                                                } }}">
                                                    {{ ucfirst($submission->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('cac.show', $submission->reference) }}" class="btn btn-xs btn-outline-primary">
                                                    View Details
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted">No history found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $submissions->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Modals -->
    @foreach ($submissions as $submission)
    <div class="modal fade" id="submissionModal{{ $submission->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold text-white">Submission Details - {{ $submission->reference }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($submission->cac_file || $submission->memart_file || $submission->status_report_file || $submission->tin_file)
                        <div class="card mb-3 border-success">
                            <div class="card-header bg-success text-white py-2">
                                <h6 class="mb-0 fw-bold text-white small">Generated Documents</h6>
                            </div>
                            <div class="card-body py-2">
                                <div class="row g-2">
                                    @if($submission->cac_file)
                                        <div class="col-md-3">
                                            <a href="{{ asset('storage/'.$submission->cac_file) }}" target="_blank" class="btn btn-sm btn-outline-success w-100">CAC Cert</a>
                                        </div>
                                    @endif
                                    @if($submission->memart_file)
                                        <div class="col-md-3">
                                            <a href="{{ asset('storage/'.$submission->memart_file) }}" target="_blank" class="btn btn-sm btn-outline-success w-100">MEMART</a>
                                        </div>
                                    @endif
                                    @if($submission->status_report_file)
                                        <div class="col-md-3">
                                            <a href="{{ asset('storage/'.$submission->status_report_file) }}" target="_blank" class="btn btn-sm btn-outline-success w-100">Status Report</a>
                                        </div>
                                    @endif
                                    @if($submission->tin_file)
                                        <div class="col-md-3">
                                            <a href="{{ asset('storage/'.$submission->tin_file) }}" target="_blank" class="btn btn-sm btn-outline-success w-100">TIN Cert</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small fw-bold d-block">SERVICE TYPE</label>
                            <span class="text-dark">{{ $submission->field_name ?? 'CAC Registration' }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small fw-bold d-block">APPLICANT NAME</label>
                            <span class="text-dark">{{ $submission->first_name }} {{ $submission->middle_name }} {{ $submission->last_name }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small fw-bold d-block">CONTACT INFO</label>
                            <span class="text-dark">{{ $submission->email }}<br>{{ $submission->phone_number }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small fw-bold d-block">SUBMISSION DATE</label>
                            <span class="text-dark">{{ $submission->submission_date->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>

                    <hr>

                    @php
                        $details = $submission->field;
                        if (is_string($details)) {
                            $details = json_decode($details, true);
                        }
                    @endphp

                    @if($details && is_array($details))
                        <h6 class="fw-bold text-primary mb-3">Business Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="text-muted small d-block">BUSINESS NAME</label>
                                <span class="fw-semibold">{{ $details['business_name_1'] ?? $submission->company_name ?? 'N/A' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">BUSINESS TYPE</label>
                                <span>{{ $details['business_type'] ?? $submission->company_type ?? 'N/A' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">NATURE OF BUSINESS</label>
                                <span>{{ $details['nature_of_business'] ?? 'N/A' }}</span>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted small d-block">BUSINESS ADDRESS</label>
                                <small>
                                    {{ $details['bus_house_no'] ?? '' }} {{ $details['bus_street'] ?? '' }}, 
                                    {{ $details['bus_city'] ?? '' }}, {{ $details['bus_lga'] ?? '' }}, 
                                    {{ $details['bus_state'] ?? '' }}
                                </small>
                            </div>
                        </div>

                        @if(!empty($details['director2_first_name']) || !empty($submission->director2_first_name))
                            <div class="p-3 bg-light rounded border mb-4">
                                <h6 class="fw-bold text-primary mb-2 small">DIRECTOR 2 DETAILS</h6>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Name:</small>
                                        <span>
                                            {{ $submission->director2_first_name ?? $details['director2_first_name'] ?? '' }} 
                                            {{ $submission->director2_surname ?? $details['director2_surname'] ?? '' }}
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Phone:</small>
                                        <span>{{ $submission->director2_phone ?? $details['director2_phone_number'] ?? 'N/A' }}</span>
                                    </div>
                                    @if(!empty($submission->director2_address))
                                    <div class="col-12">
                                        <small class="text-muted d-block">Address:</small>
                                        <span>{{ $submission->director2_address }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <h6 class="fw-bold text-primary mb-3">Supporting Documents</h6>
                        <div class="row g-2 mb-2">
                            @if(isset($details['uploads']['nin']) || $submission->nin_slip_url)
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/'.($details['uploads']['nin'] ?? $submission->nin_slip_url)) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Director 1 NIN
                                    </a>
                                </div>
                            @endif
                            @if(isset($details['uploads']['signature']))
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/'.$details['uploads']['signature']) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="bi bi-file-earmark-image me-1"></i>Director 1 Signature
                                    </a>
                                </div>
                            @endif
                            @if(isset($details['uploads']['passport']) || $submission->passport_url)
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/'.($details['uploads']['passport'] ?? $submission->passport_url)) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="bi bi-person-badge me-1"></i>Director 1 Photo
                                    </a>
                                </div>
                            @endif
                        </div>

                        @if(isset($details['uploads']['director2_nin']))
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/'.$details['uploads']['director2_nin']) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-info w-100">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>Director 2 NIN
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/'.$details['uploads']['director2_signature']) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-info w-100">
                                        <i class="bi bi-file-earmark-image me-1"></i>Director 2 Signature
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ asset('storage/'.$details['uploads']['director2_passport']) }}" 
                                       target="_blank" class="btn btn-sm btn-outline-info w-100">
                                        <i class="bi bi-person-badge me-1"></i>Director 2 Photo
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning small">No additional details found for this submission.</div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @if($submission->status === 'query')
                        <button type="button" class="btn btn-primary">Respond to Query</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceSelect = document.getElementById('service_field');
            const priceDisplay = document.getElementById('price-display');
            const businessDetailsSection = document.getElementById('business-details-section');
            const steps = document.querySelectorAll('.wizard-step');
            const nextButtons = document.querySelectorAll('.next-step');
            const prevButtons = document.querySelectorAll('.prev-step');
            const skipButtons = document.querySelectorAll('.skip-step');
            const form = document.getElementById('cacForm');
            const submitBtn = document.getElementById('submitBtn');
            
            let currentStep = 0;

            // Price display and Business Details toggle
            if (serviceSelect) {
                serviceSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    
                    if (price) {
                        priceDisplay.textContent = '₦' + new Intl.NumberFormat().format(price);
                        businessDetailsSection.classList.remove('d-none');
                    } else {
                        priceDisplay.textContent = '₦0.00';
                        businessDetailsSection.classList.add('d-none');
                    }
                });

                // Trigger change on load if value exists
                if (serviceSelect.value) {
                    serviceSelect.dispatchEvent(new Event('change'));
                }
            }

            // Navigation Functions
            function showStep(stepIndex) {
                steps.forEach((step, index) => {
                    if (index === stepIndex) {
                        step.classList.remove('d-none');
                    } else {
                        step.classList.add('d-none');
                    }
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function validateStep(stepIndex) {
                const currentStepEl = steps[stepIndex];
                const requiredInputs = currentStepEl.querySelectorAll('[required]');
                let isValid = true;

                // Special check for Step 1
                if (stepIndex === 0) {
                    if (!serviceSelect.value) {
                        serviceSelect.classList.add('is-invalid');
                        alert('Please select a Registration Type.');
                        return false;
                    } else {
                        serviceSelect.classList.remove('is-invalid');
                    }
                }

                requiredInputs.forEach(input => {
                    // Skip hidden inputs or inputs in hidden containers
                    if (input.offsetParent === null) return;

                    if (input.type === 'checkbox') {
                        if (!input.checked) {
                            isValid = false;
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    } else if (input.type === 'file') {
                        if (!input.files || input.files.length === 0) {
                            isValid = false;
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    } else {
                        if (!input.value.trim()) {
                            isValid = false;
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    }
                });

                if (!isValid) {
                    const firstInvalid = currentStepEl.querySelector('.is-invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                return isValid;
            }

            // Handle final form submission
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateStep(currentStep)) {
                        e.preventDefault();
                    } else {
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Submitting...';
                        }
                    }
                });
            }

            // Next button handlers
            nextButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (validateStep(currentStep)) {
                        if (currentStep < steps.length - 1) {
                            currentStep++;
                            showStep(currentStep);
                        }
                    }
                });
            });

            // Previous button handlers
            prevButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (currentStep > 0) {
                        currentStep--;
                        showStep(currentStep);
                    }
                });
            });

            // Skip button handlers (for director 2 step)
            skipButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Clear all inputs in step 3 when skipping
                    if (currentStep === 2) {
                        const inputs = steps[currentStep].querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            if (input.type !== 'button' && input.type !== 'submit') {
                                input.value = '';
                                if (input.type === 'file') {
                                    input.value = '';
                                }
                                input.classList.remove('is-invalid');
                            }
                        });
                    }
                    
                    if (currentStep < steps.length - 1) {
                        currentStep++;
                        showStep(currentStep);
                    }
                });
            });
        });
    </script>
    @endpush

    <style>
        .wizard-step { transition: opacity 0.3s ease-in-out; }
        .is-invalid { border-color: #dc3545 !important; }
        .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
    </style>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</x-app-layout>