<x-app-layout>
    <title>Baya Jidda - {{ $title ?? 'Dashboard' }}</title>

    <!-- Announcement Banner -->
    @if(isset($announcement) && $announcement)
    <div class="notification-container mt-3 mb-2">
        <div class="scrolling-text-container bg-primary text-white shadow-sm rounded-3 py-2">
            <div class="scrolling-text">
                <span class="fw-bold me-3"><i class="fas fa-bullhorn"></i> ANNOUNCEMENT:</span>
                {{ $announcement->message }}
            </div>
        </div>
    </div>
    @endif

    <div class="mt-4">
        <!-- User + Wallet Section -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body user-wallet-wrap">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <!-- User Image -->
                    <div class="avatar flex-shrink-0">
                        <img src="{{ Auth::user()->photo ?? asset('assets/img/profiles/avatar-31.jpg') }}"
                             class="rounded-circle border border-3 border-primary shadow-sm user-avatar"
                             alt="User Avatar">
                    </div>

                    <!-- Welcome Message -->
                    <div class="me-auto">
                        <h4 class="fw-semibold text-dark mb-1 welcome-text">
                            Welcome back, {{ Auth::user()->first_name . ' ' . Auth::user()->surname ?? 'User' }} 👋
                        </h4>
                        <small class="text-danger">Account ID: {{ $virtualAccount->accountNo ?? 'N/A' }} {{ $virtualAccount->bankName ?? 'N/A' }}</small>
                    </div>

                    <!-- Wallet Info -->
                    <div class="d-flex align-items-center gap-2 ms-2">
                        <span class="fw-medium text-muted small mb-0">Balance:</span>
                        <h5 id="wallet-balance" class="mb-0 text-success fw-bold balance-text">
                            ₦{{ number_format($wallet->balance ?? 0, 2) }}
                        </h5>

                        <!-- Toggle Balance Button -->
                        <button id="toggle-balance" class="btn btn-sm btn-outline-secondary ms-1 p-1 toggle-btn"
                                aria-pressed="true" title="Toggle balance visibility">
                            <i class="fas fa-eye eye-icon" aria-hidden="true"></i>
                        </button>

                        <!-- Wallet Icon -->
                        <a href="{{ route('wallet') }}" class="btn btn-light ms-1 border-0 p-0 wallet-btn"
                           title="View Wallet Details" aria-label="View wallet">
                            <i class="fas fa-wallet wallet-icon text-primary"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        @include('pages.alart')

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4 d-none d-md-flex">
            <!-- Total Spent -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Total Spent</p>
                                <h4 class="fw-bold mb-0">₦{{ number_format($totalTransactionAmount, 2) }}</h4>
                            </div>
                            <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-arrow-down text-danger fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Funded -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Total Funded</p>
                                <h4 class="fw-bold mb-0">₦{{ number_format($totalFundedAmount, 2) }}</h4>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-arrow-up text-success fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Agency Requests -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Agency Requests</p>
                                <h4 class="fw-bold mb-0">{{ $totalAgencyRequests }}</h4>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-briefcase text-primary fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Referrals -->
            <div class="col-xl-3 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">Referrals</p>
                                <h4 class="fw-bold mb-0">{{ $totalReferrals }}</h4>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-users text-warning fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Services Section -->
        <section class="py-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-bolt text-warning"></i>
                        <h5 class="fw-bold mb-0">Quick Services</h5>
                    </div>
                    <p class="text-muted small mb-4">Instant access to popular payments</p>

                    <div class="row row-cols-3 row-cols-md-6 g-4 text-center">
                        <!-- Fund Wallet -->
                        <div class="col">
                            <a href="{{ route('wallet') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-wallet fs-24 text-primary"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Fund Wallet</span>
                            </a>
                        </div>

                        <!-- Airtime -->
                        <div class="col">
                            <a href="{{ route('airtime') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-phone-call fs-24 text-info"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Airtime</span>
                            </a>
                        </div>

                        <!-- Data -->
                        <div class="col">
                            <a href="{{ route('buy-data') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-world fs-24 text-warning"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Data</span>
                            </a>
                        </div>

                        <!-- SME Data -->
                        <div class="col">
                            <a href="{{ route('buy-sme-data') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-device-gamepad fs-24 text-success"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">SME Data</span>
                            </a>
                        </div>

                        <!-- Electricity -->
                        <div class="col">
                            <a href="{{ route('electricity') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-bolt fs-24 text-danger"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Electricity</span>
                            </a>
                        </div>

                        <!-- Education -->
                        <div class="col">
                            <a href="{{ route('education') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-school fs-24 text-primary"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Education</span>
                            </a>
                        </div>

                        <!-- BVN Services -->
                        <div class="col">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#bvnServicesModal" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <img src="https://img.icons8.com/color/48/bank-card-back-side.png" class="service-img-icon" alt="BVN">
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">BVN Services</span>
                            </a>
                        </div>

                        <!-- NIN Services -->
                        <div class="col">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#ninServicesModal" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <img src="https://img.icons8.com/color/48/identification-documents.png" class="service-img-icon" alt="NIN">
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">NIN Services</span>
                            </a>
                        </div>

                        <!-- Verify -->
                        <div class="col">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#verifyModal" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-id-badge fs-24 text-secondary"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Verify</span>
                            </a>
                        </div>

                        <!-- Hotel -->
                        <div class="col">
                            <a href="{{ route('hotel.index') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-home-plus fs-24 text-success"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Hotel</span>
                            </a>
                        </div>

                        <!-- Travel -->
                        <div class="col">
                            <a href="{{ route('travel.index') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-plane fs-24 text-success"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Travel</span>
                            </a>
                        </div>

                        <!-- Support -->
                        <div class="col">
                            <a href="{{ route('support') }}" class="text-decoration-none service-item">
                                <div class="service-icon-wrap mb-2 mx-auto">
                                    <i class="ti ti-headset fs-24 text-info"></i>
                                </div>
                                <span class="fs-13 fw-medium text-dark d-block">Support</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Transactions & Statistics Row -->
        <div class="row g-4 d-none d-lg-flex">
            <!-- Recent Transactions -->
            <div class="col-xxl-8 col-xl-7 d-flex">
                <div class="card flex-fill border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap border-bottom-0">
                        <h5 class="mb-0 fw-bold text-dark">Recent Transactions</h5>
                        <a href="{{ route('transactions') }}" class="btn btn-sm btn-light text-primary fw-medium">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">  
                            <table class="table table-hover table-nowrap mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-secondary small fw-semibold ps-4">#</th>
                                        <th class="text-secondary small fw-semibold">Ref ID</th>
                                        <th class="text-secondary small fw-semibold">Type</th>
                                        <th class="text-secondary small fw-semibold">Amount</th>
                                        <th class="text-secondary small fw-semibold">Date</th>
                                        <th class="text-secondary small fw-semibold pe-4 text-end">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="text-muted small">{{ $loop->iteration }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium text-dark">#{{ substr($transaction->transaction_ref, 0, 8) }}...</span>
                                        </td>
                                        <td>
                                            @if($transaction->type == 'credit')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                                    <i class="ti ti-arrow-down-left me-1"></i>Credit
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                                    <i class="ti ti-arrow-up-right me-1"></i>Debit
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold {{ $transaction->type == 'credit' ? 'text-success' : 'text-danger' }}">
                                                {{ $transaction->type == 'credit' ? '+' : '-' }}₦{{ number_format($transaction->amount, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            @if($transaction->status == 'completed' || $transaction->status == 'successful')
                                                <span class="badge bg-success text-white rounded-pill px-3">Success</span>
                                            @elseif($transaction->status == 'pending')
                                                <span class="badge bg-warning text-white rounded-pill px-3">Pending</span>
                                            @else
                                                <span class="badge bg-danger text-white rounded-pill px-3">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ti ti-receipt-off fs-1 text-muted mb-2"></i>
                                                <p class="text-muted mb-0">No recent transactions found.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Statistics -->
            <div class="col-xxl-4 col-xl-5 d-none d-xl-flex">
                <div class="card flex-fill border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h5 class="mb-0 fw-bold text-dark">Transaction Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="position-relative mb-4 d-flex justify-content-center">
                            <div style="height: 200px; width: 200px;">
                                <canvas id="transactionChart" 
                                        data-completed="{{ $completedTransactions }}"
                                        data-pending="{{ $pendingTransactions }}"
                                        data-failed="{{ $failedTransactions }}"></canvas>
                            </div>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <p class="fs-12 text-muted mb-0">Total</p>
                                <h3 class="fw-bold text-dark mb-0">{{ $totalTransactions }}</h3>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-success-subtle text-center h-100">
                                    <i class="ti ti-circle-check-filled fs-4 text-success mb-2"></i>
                                    <h6 class="fw-bold text-dark mb-1">{{ $completedPercentage }}%</h6>
                                    <span class="fs-11 text-muted text-uppercase fw-semibold">Success</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-warning-subtle text-center h-100">
                                    <i class="ti ti-clock-filled fs-4 text-warning mb-2"></i>
                                    <h6 class="fw-bold text-dark mb-1">{{ $pendingPercentage }}%</h6>
                                    <span class="fs-11 text-muted text-uppercase fw-semibold">Pending</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-danger-subtle text-center h-100">
                                    <i class="ti ti-circle-x-filled fs-4 text-danger mb-2"></i>
                                    <h6 class="fw-bold text-dark mb-1">{{ $failedPercentage }}%</h6>
                                    <span class="fs-11 text-muted text-uppercase fw-semibold">Failed</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold text-primary mb-1">₦{{ number_format($totalTransactionAmount, 2) }}</h5>
                                <p class="fs-12 text-muted mb-0">Total Spent This Month</p>
                            </div>
                            <a href="{{ route('transactions') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                View Report <i class="ti ti-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verify Modal -->
    <div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-verif">
                <div class="modal-header text-white">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="verifyModalLabel">
                        <i class="ti ti-id-badge fs-3"></i>
                        Verification Services
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        @php
                            $verifyServices = [
                                ['route' => route('nin.verification.index'), 'icon' => 'ti-fingerprint', 'bg' => 'icon-bg-primary', 'name' => 'Verify NIN'],
                                ['route' => route('nin.phone.index'), 'icon' => 'ti-phone', 'bg' => 'icon-bg-info', 'name' => 'Verify Phone No'],
                                ['route' => route('nin.demo.index'), 'icon' => 'ti-user-check', 'bg' => 'icon-bg-secondary', 'name' => 'Verify DEMO'],
                                ['route' => route('bvn.verification.index'), 'icon' => 'ti-shield-check', 'bg' => 'icon-bg-success', 'name' => 'Verify BVN'],
                                ['route' => route('tin.index'), 'icon' => 'ti-briefcase', 'bg' => 'icon-bg-warning', 'name' => 'Verify TIN'],
                            ];
                        @endphp
                        @foreach ($verifyServices as $sv)
                            <div class="col-4">
                                <a href="{{ $sv['route'] }}" class="modal-service-card">
                                    <div class="modal-service-icon {{ $sv['bg'] }}">
                                        <i class="ti {{ $sv['icon'] }}"></i>
                                    </div>
                                    <h6 class="modal-service-title">{{ $sv['name'] }}</h6>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BVN Services Modal -->
    <div class="modal fade" id="bvnServicesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-bvn">
                <div class="modal-header text-white">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="ti ti-id-badge fs-3"></i>
                        BVN Services
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        @php
                            $bvnServices = [
                                ['route' => route('modification'), 'icon' => 'ti-edit', 'bg' => 'icon-bg-primary', 'name' => 'Modification'],
                                ['route' => route('bvn-crm'), 'icon' => 'ti-headset', 'bg' => 'icon-bg-info', 'name' => 'BVN CRM'],
                                ['route' => route('phone.search.index'), 'icon' => 'ti-search', 'bg' => 'icon-bg-success', 'name' => 'BVN Search'],
                            ];
                        @endphp
                        @foreach ($bvnServices as $sv)
                            <div class="col-4">
                                <a href="{{ $sv['route'] }}" class="modal-service-card">
                                    <div class="modal-service-icon {{ $sv['bg'] }}">
                                        <i class="ti {{ $sv['icon'] }}"></i>
                                    </div>
                                    <h6 class="modal-service-title">{{ $sv['name'] }}</h6>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NIN Services Modal -->
    <div class="modal fade" id="ninServicesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-nin">
                <div class="modal-header text-white">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="ti ti-id-badge fs-3"></i>
                        NIN Services
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        @php
                            $ninServices = [
                                ['route' => route('nin-validation'), 'icon' => 'ti-check', 'bg' => 'icon-bg-warning', 'name' => 'Validation'],
                                ['route' => route('nin-modification'), 'icon' => 'ti-edit', 'bg' => 'icon-bg-success', 'name' => 'Modification'],
                                ['route' => route('ipe.index'), 'icon' => 'ti-users', 'bg' => 'icon-bg-danger', 'name' => 'IPE Services'],
                            ];
                        @endphp
                        @foreach ($ninServices as $sv)
                            <div class="col-4">
                                <a href="{{ $sv['route'] }}" class="modal-service-card">
                                    <div class="modal-service-icon {{ $sv['bg'] }}">
                                        <i class="ti {{ $sv['icon'] }}"></i>
                                    </div>
                                    <h6 class="modal-service-title">{{ $sv['name'] }}</h6>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>




    

    @push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    @endpush
</x-app-layout>
