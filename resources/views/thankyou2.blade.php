<x-app-layout>
    <title>Baya Jidda - Educational Pin Receipt</title>

    @push('styles')
    <style>
        .receipt-container { padding: 2rem 1rem; min-height: 80vh; }
        
        .receipt-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 500px;
            margin: 0 auto;
            animation: slideUp 0.5s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .receipt-body { padding: 2.5rem 2rem; }
        
        .transaction-title {
            text-align: center;
            color: #1a1d1f;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(13,92,62,0.05), rgba(13,92,62,0.1));
            border-radius: 8px;
            border: 1px dashed rgba(13,92,62,0.3);
        }
        
        .details-stack {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .detail-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.25rem;
        }
        
        .detail-box h6.section-label {
            color: #0D5C3E;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.5rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .detail-item:last-child { border-bottom: none; }
        .detail-item p { margin: 0; color: #6c757d; font-size: 0.85rem; }
        .detail-item h6 { margin: 0; color: #1a1d1f; font-weight: 600; font-size: 0.9rem; }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.4rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 2px 8px rgba(40,167,69,0.2);
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px dashed #dee2e6;
        }
        
        .btn-download {
            background: #0D5C3E;
            color: white;
            border: none;
            padding: 0.85rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(13,92,62,0.2);
        }
        
        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(13,92,62,0.3);
            color: white;
        }
        
        .btn-buy-again {
            background: white;
            color: #0D5C3E;
            border: 2px solid #0D5C3E;
            padding: 0.85rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-buy-again:hover {
            background: #0D5C3E;
            color: white;
            transform: translateY(-2px);
        }
        
        .footer-note {
            text-align: center;
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .receipt-brand {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .receipt-brand h4 {
            color: #0D5C3E;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .receipt-body { padding: 1.5rem; }
        }
    </style>
    @endpush

    <div class="receipt-container">
        <!-- Receipt Card -->
        <div class="receipt-card" id="receiptContent">
            <div class="receipt-body">
                <!-- Brand Header -->
                <div class="receipt-brand">
                    <h4>Bayajidda Global Services</h4>
                    <p class="text-muted small mb-0">Official Transaction Receipt</p>
                </div>

                <!-- Transaction Status -->
                <div class="text-center mb-4">
                    <span class="status-badge">
                        <i class="bi bi-check-circle-fill"></i>
                        Success
                    </span>
                </div>

                <!-- Transaction Title -->
                <h5 class="transaction-title">
                    Educational Pin Receipt
                </h5>

                <!-- Combined Details Stack -->
                <div class="details-stack">
                    <div class="detail-box">
                        <h6 class="section-label">Purchase Info</h6>
                        <div class="detail-item">
                            <p>Exam Name</p>
                            <h6>{{ session('network') }}</h6>
                        </div>
                        <div class="detail-item">
                            <p>Reference</p>
                            <h6>{{ session('ref') }}</h6>
                        </div>
                        
                        @if(session('pins'))
                        <div class="mt-3 border-top pt-2">
                            <p class="text-muted small mb-2 fw-bold uppercase">Purchased PINs</p>
                            @php
                                $pins = session('pins');
                                if (is_string($pins)) {
                                    $pins = json_decode($pins, true) ?? [$pins];
                                }
                            @endphp
                            <div class="pins-list">
                                @if(is_array($pins))
                                    @foreach($pins as $index => $pin)
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span class="text-muted small">Pin {{ $index + 1 }}:</span>
                                            <h6 class="text-primary fw-bold mb-0">{{ $pin }}</h6>
                                        </div>
                                    @endforeach
                                @else
                                    <h6 class="text-primary fw-bold">{{ $pins }}</h6>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="detail-item mt-3">
                            <p>Amount Paid</p>
                            <h6 class="text-success">₦{{ number_format(session('amount', 0), 2) }}</h6>
                        </div>
                        <div class="detail-item">
                            <p>Date</p>
                            <h6>{{ now()->format('d M, Y H:i') }}</h6>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="footer-note">
                    <p class="mb-1"><strong>Thank you for choosing Baya Jidda!</strong></p>
                    <p class="mb-0">Please keep your PINs safe. Computer generated receipt.</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons (Outside receipt) -->
        <div class="action-buttons-container" style="max-width: 500px; margin: 0 auto; padding: 0 1rem;">
            <div class="action-buttons">
                <button onclick="downloadReceipt()" class="btn btn-download">
                    <i class="bi bi-download me-2"></i>Download Receipt
                </button>
                <a href="{{ route('education') }}" class="btn btn-buy-again">
                    <i class="bi bi-arrow-repeat me-2"></i>Buy Again
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function downloadReceipt() {
            const receiptElement = document.getElementById('receiptContent');
            const downloadBtn = event.target.closest('.btn-download');
            const originalHTML = downloadBtn.innerHTML;
            
            downloadBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Generating...';
            downloadBtn.disabled = true;

            html2canvas(receiptElement, {
                scale: 3,
                backgroundColor: '#ffffff',
                logging: false,
                useCORS: true,
                windowWidth: 500
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Receipt_{{ session("ref", "receipt") }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                downloadBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Downloaded!';
                setTimeout(() => {
                    downloadBtn.innerHTML = originalHTML;
                    downloadBtn.disabled = false;
                }, 2000);
            }).catch(error => {
                console.error('Error generating receipt:', error);
                alert('Failed to generate receipt. Please try again.');
                downloadBtn.innerHTML = originalHTML;
                downloadBtn.disabled = false;
            });
        }
    </script>
    @endpush
</x-app-layout>
