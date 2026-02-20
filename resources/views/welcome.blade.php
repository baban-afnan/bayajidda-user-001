<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Baya Jidda - {{ $title ?? 'Welcome to Baya Jidda' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
        
        <style>
            :root {
                --primary-color: #002fba;
                --primary-dark: #002696;
                --primary-light: #4d6be0;
                --ease-out: cubic-bezier(0.33, 1, 0.68, 1);
            }

            /* Header & Nav */
            header { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 15px 0; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05); }
            .header-container { display: flex; justify-content: space-between; align-items: center; }
            nav ul { display: flex; list-style: none; margin: 0; align-items: center; }
            nav ul li { margin-left: 30px; }
            nav ul li a { text-decoration: none; color: #333; font-weight: 600; transition: all 0.3s ease; }
            nav ul li a:hover { color: var(--primary-color); }

            /* Hero Section Premium */
            .hero-title { font-size: 4.5rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 30px; text-shadow: 0 10px 30px rgba(0,0,0,0.3); }
            .hero-subtitle { font-size: 1.4rem; color: rgba(255,255,255,0.95); max-width: 800px; margin: 0 auto; line-height: 1.6; text-shadow: 0 5px 15px rgba(0,0,0,0.2); }
            
            /* Testimonials Premium */
            .testimonials-section { padding: 120px 0; background: linear-gradient(135deg, #002fba 0%, #001f7a 100%); position: relative; }
            .testimonial-card-premium { background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.15); padding: 45px; border-radius: 30px; color: white; height: 100%; transition: all 0.4s var(--ease-out); }
            .testimonial-card-premium:hover { transform: translateY(-15px); background: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.3); }
            .quote-icon { font-size: 2.5rem; color: #60a5fa; margin-bottom: 25px; opacity: 0.8; }
            .review-text { font-size: 1.15rem; line-height: 1.8; margin-bottom: 35px; font-style: italic; opacity: 0.95; }
            .reviewer-info { display: flex; align-items: center; gap: 20px; }
            .reviewer-info img { width: 65px; height: 65px; border-radius: 50%; border: 3px solid rgba(255,255,255,0.3); }
            .reviewer-info h4 { font-size: 1.25rem; font-weight: 700; margin: 0; }
            .reviewer-info span { font-size: 0.95rem; opacity: 0.7; }

            /* Footer Premium */
            .footer-main { background: #0f172a; color: #fff; padding: 100px 0 50px; position: relative; overflow: hidden; }
            .footer-brand h2 { font-size: 2.5rem; font-weight: 900; background: linear-gradient(90deg, #fff, #94a3b8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; margin: 0; }
            .footer-widget-title { font-size: 1.3rem; font-weight: 800; margin-bottom: 30px; position: relative; padding-bottom: 15px; }
            .footer-widget-title::after { content: ''; position: absolute; bottom: 0; left: 0; width: 40px; height: 4px; background: var(--primary-color); border-radius: 2px; }
            .footer-list { list-style: none; padding: 0; margin: 0; }
            .footer-list li { margin-bottom: 15px; transition: transform 0.3s ease; }
            .footer-list li:hover { transform: translateX(10px); }
            .footer-list a { color: #94a3b8; text-decoration: none; transition: color 0.3s ease; font-size: 0.95rem; }
            .footer-list a:hover { color: #fff; }
            .footer-contact-item { display: flex; align-items: flex-start; gap: 15px; margin-bottom: 20px; }
            .contact-icon { width: 35px; height: 35px; background: rgba(255,255,255,0.05); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1rem; flex-shrink: 0; }
            .footer-social-links { display: flex; gap: 15px; margin-top: 30px; }
            .social-circle { width: 45px; height: 45px; background: rgba(255,255,255,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none; transition: all 0.3s ease; border: 1px solid rgba(255,255,255,0.1); }
            .social-circle:hover { background: var(--primary-color); transform: translateY(-5px) rotate(360deg); border-color: var(--primary-color); }
            
            /* Newsletter */
            .newsletter-box { background: rgba(255,255,255,0.03); padding: 30px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); margin-top: 40px; }
            .newsletter-form .form-control { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; border-radius: 12px; padding: 12px 20px; }
            .newsletter-form .form-control:focus { background: rgba(255,255,255,0.08); border-color: var(--primary-color); box-shadow: none; }
            
            /* Privacy Banner Premium */
            .privacy-banner-refined { position: fixed; bottom: 30px; left: 30px; right: 30px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); padding: 25px 40px; border-radius: 25px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.5); z-index: 9999; display: none; animation: slideUpBanner 0.6s var(--ease-out); }
            @keyframes slideUpBanner { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

            @media (max-width: 768px) {
                .hero-title { font-size: 2.8rem; }
                .footer-brand h2 { font-size: 2rem; }
                .privacy-banner-refined { bottom: 20px; left: 20px; right: 20px; padding: 20px; border-radius: 20px; }
            }
        </style>

        <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/logo/favicon.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/logo/favicon.png') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <!-- Open Graph / WhatsApp Meta Tags -->
        <meta property="og:title" content="Baya Jidda - Innovative Digital Solutions">
        <meta property="og:description" content="Empowering northern Nigeria through innovative digital solutions and smart technology services.">
        <meta property="og:image" content="{{ asset('assets/img/logo/logo.png') }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:type" content="website">
    </head>

    <body class="bg-white">
        <div id="global-loader" style="display: none;">
            <div class="page-loader"></div>
        </div>

        <!-- Header -->
        <header>
            <div class="container header-container">
                <a href="#" class="logo">
                    <img src="{{ asset('assets/img/logo/logo.png') }}" alt="Baya Jidda" style="height: 40px; margin-right: 10px;">
                </a>
                <div class="d-flex align-items-center ms-auto ms-lg-0">
                    <a href="{{route ('login')}}" class="btn btn-primary btn-sm text-white me-3 d-inline-block d-lg-none" style="white-space: nowrap;">Get Started</a>
                    <div class="mobile-menu">
                        <i class="fas fa-bars"></i>
                    </div>
                </div>
                <nav>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="#partners">Partners</a></li>
                        <li><a href="#support">Support</a></li>
                        <li><a href="#about-us">About Us</a></li>
                        <li class="d-none d-lg-block"><a href="{{route ('login')}}" class="btn btn-primary text-white">Get Started</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="hero" id="home" style="background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 47, 186, 0.4)), 
             url('{{ asset('assets/images/logo/hero.jpg') }}') no-repeat center center/cover; min-height: 100vh; display: flex; align-items: center;">
            <div class="container hero-content text-center">
                <h1 class="text-dark mb-4" data-aos="fade-down" data-aos-duration="1000" style="font-size: 3.5rem; font-weight: 800; color: #fff !important; text-shadow: 0 2px 10px rgba(0,0,0,0.3);">
                    Your Trusted Partner for Business
                </h1>
                <p class="text-white mb-5" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000" style="font-size: 1.25rem; max-width: 800px; margin: 0 auto; line-height: 1.6; text-shadow: 0 1px 5px rgba(0,0,0,0.3);">
                    Complete Agency & Verification Services – Utility Bill Payments, Identity Verification, Travel Booking, and Hotel Reservations for Your Business.
                </p>
                <div class="hero-btns" data-aos="fade-up" data-aos-delay="400" data-aos-duration="1000">
                    <a href="{{route ('register')}}" class="btn btn-primary btn-lg me-3">
                        Get Started
                    </a>
                    <a href="{{route ('login')}}" class="btn btn-secondary btn-lg me-3">
                        Login Now
                    </a>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        @include('pages.landing.services')

        <!-- Testimonials Section -->
        <section id="testimonials" class="testimonials-section" style="padding: 100px 0; background: linear-gradient(135deg, #002fba 0%, #001f7a 100%);">
            <!-- Background Patterns -->
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0.05; background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            
            <div class="container" style="position: relative; z-index: 2;">
                <div class="section-title text-center mb-5" data-aos="fade-up">
                    <h4 style="color: rgba(255,255,255,0.8); font-weight: 600; letter-spacing: 3px; text-transform: uppercase; font-size: 0.9rem;">Testimonials</h4>
                    <h2 style="color: #fff; font-weight: 800; font-size: 2.8rem; margin-top: 10px;">Trusted by Leaders</h2>
                    <hr style="width: 80px; height: 4px; background: #fff; margin: 20px auto; border: none; border-radius: 2px;">
                    <p class="text-white-50" style="max-width: 650px; margin: 0 auto; font-size: 1.15rem;">
                        See what our partners and clients have to say about their experience working with Digital Verify.
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Testimonial 1 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="testimonial-card-premium">
                            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                            <p class="review-text">"Digital Verify Sub transformed our operations with cutting-edge solutions. Their support team is always responsive and professional! Truly a game changer for our business."</p>
                            <div class="reviewer-info">
                                <img src="{{ asset('assets/images/avatar/avatar-8.jpg') }}" alt="Abdulrahman Musa">
                                <div>
                                    <h4>Abdulrahman Musa</h4>
                                    <span>CEO, NorthernTech</span>
                                    <div class="stars">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 2 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="testimonial-card-premium">
                            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                            <p class="review-text">"Working with Digital Verify Sub has been a seamless experience. Their expertise and attention to detail are unmatched. They delivered exactly what we needed, on time."</p>
                            <div class="reviewer-info">
                                <img src="{{ asset('assets/images/avatar/avatar-3.jpg') }}" alt="Fatima Bello">
                                <div>
                                    <h4>Fatima Bello</h4>
                                    <span>Manager, Digital Verify Sub Logistics</span>
                                    <div class="stars">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonial 3 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="testimonial-card-premium">
                            <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                            <p class="review-text">"The quality of service and support we've received from Digital Verify Sub is outstanding. Highly recommended for any business looking to scale digitally."</p>
                            <div class="reviewer-info">
                                <img src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" alt="Emeka Johnson">
                                <div>
                                    <h4>Emeka Johnson</h4>
                                    <span>IT Director, Digital Verify Sub Ltd</span>
                                    <div class="stars">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @include('pages.landing.about-us')
        </div>
        @include('pages.landing.support')
        
        <!-- Footer -->
        <!-- Footer Section -->
        <footer class="footer-main">
            <!-- Background Decoration -->
            <div style="position: absolute; top: -100px; right: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(0, 47, 186, 0.15) 0%, rgba(0,0,0,0) 70%); filter: blur(50px);"></div>
            <div style="position: absolute; bottom: -50px; left: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(0, 47, 186, 0.1) 0%, rgba(0,0,0,0) 70%); filter: blur(30px);"></div>

            <div class="container" style="position: relative; z-index: 2;">
                <div class="row g-5">
                    <!-- Company Info -->
                    <div class="col-lg-4 col-md-12">
                        <div class="footer-brand mb-4">
                            <h2>Baya Jidda</h2>
                        </div>
                        <p style="color: #94a3b8; line-height: 1.8; margin-bottom: 30px; font-size: 1.05rem;">
                            Empowering Northern Nigeria with innovative digital solutions. We bridge the gap between technology and business excellence through reliable, secure, and smart agency services.
                        </p>
                        <div class="footer-social-links">
                            <a href="#" class="social-circle"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-circle"><i class="fab fa-x-twitter"></i></a>
                            <a href="#" class="social-circle"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-circle"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="col-lg-2 col-md-6 col-6">
                        <h3 class="footer-widget-title text-white">Quick Links</h3>
                        <ul class="footer-list">
                            <li><a href="#home">Home</a></li>
                            <li><a href="#services">Services</a></li>
                            <li><a href="#partners">Partners</a></li>
                            <li><a href="#support">Support</a></li>
                            <li><a href="#about-us">About Us</a></li>
                        </ul>
                    </div>
                    
                    <!-- Services -->
                    <div class="col-lg-3 col-md-6 col-6">
                        <h3 class="footer-widget-title text-white">Our Services</h3>
                        <ul class="footer-list">
                            <li><a href="#">BVN Verification</a></li>
                            <li><a href="#">NIN Services</a></li>
                            <li><a href="#">Data Bundles</a></li>
                            <li><a href="#">Utility Payments</a></li>
                            <li><a href="javascript:void(0)" onclick="openDataProtectionModal()">Privacy Policy</a></li>
                        </ul>
                    </div>
                    
                    <!-- Contact -->
                    <div class="col-lg-3 col-md-12">
                        <h3 class="footer-widget-title text-white">Contact Us</h3>
                        <div class="footer-contact-item">
                            <div class="contact-icon"><i class="fas fa-location-dot"></i></div>
                            <div class="contact-info">
                                <span style="display: block; font-weight: 600; color: #fff; margin-bottom: 5px;">Head Office</span>
                                <span style="color: #94a3b8; font-size: 0.95rem;">Tudun Wada Street, Gwammaja, Kano</span>
                            </div>
                        </div>
                        <div class="footer-contact-item">
                            <div class="contact-icon"><i class="fas fa-phone-volume"></i></div>
                            <div class="contact-info">
                                <span style="display: block; font-weight: 600; color: #fff; margin-bottom: 5px;">Call Support</span>
                                <span style="color: #94a3b8; font-size: 0.95rem;">+234 911 234 5678</span>
                            </div>
                        </div>
                        <div class="footer-contact-item">
                            <div class="contact-icon"><i class="fas fa-envelope-open-text"></i></div>
                            <div class="contact-info">
                                <span style="display: block; font-weight: 600; color: #fff; margin-bottom: 5px;">Email Us</span>
                                <span style="color: #94a3b8; font-size: 0.95rem;">safanane@gmail.com</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="newsletter-box">
                            <div class="row align-items-center">
                                <div class="col-lg-6 mb-4 mb-lg-0">
                                    <h4 class="text-white mb-2" style="font-weight: 700;">Subscribe to our Newsletter</h4>
                                    <p class="text-white-50 mb-0">Get the latest updates on our services and special offers.</p>
                                </div>
                                <div class="col-lg-6">
                                    <form class="newsletter-form">
                                        <div class="input-group">
                                            <input type="email" class="form-control" placeholder="Enter your email address" required>
                                            <button class="btn btn-primary px-4" type="submit">Subscribe Now</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <hr style="border-color: rgba(255,255,255,0.05); margin: 60px 0 30px;">
                
                <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">&copy; {{ date('Y') }} <strong>Baya Jidda</strong>. All rights reserved.</p>
                    <div class="footer-legal d-flex gap-4">
                        <a href="#" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">Terms of Service</a>
                        <a href="javascript:void(0)" onclick="openDataProtectionModal()" style="color: #64748b; text-decoration: none; font-size: 0.9rem;">Privacy Policy</a>
                    </div>
                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Designed with <i class="fas fa-heart" style="color: #ef4444;"></i> by <strong>Baya Jidda Team</strong>.</p>
                </div>
            </div>
        </footer>

        <!-- Privacy Banner (Footer) Refined -->
        <div class="privacy-banner-refined" id="privacyBanner">
            <div class="banner-content d-flex flex-column flex-lg-row align-items-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-4">
                    <div class="privacy-icon-container" style="width: 60px; height: 60px; background: rgba(0, 47, 186, 0.1); border-radius: 18px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fas fa-shield-halved"></i>
                    </div>
                    <div class="privacy-text">
                        <h5 class="fw-800 mb-1" style="color: #0f172a;">Your Privacy is Our Priority</h5>
                        <p class="mb-0" style="color: #475569; font-size: 0.95rem; max-width: 600px;">We use essential cookies and process data in compliance with NDPR to ensure our verification services are secure and reliable for you.</p>
                    </div>
                </div>
                <div class="banner-actions d-flex align-items-center gap-3">
                    <a href="javascript:void(0)" class="fw-bold text-decoration-none" style="color: var(--primary-color);" onclick="openDataProtectionModal()">Policy Details</a>
                    <button type="button" class="btn btn-light px-4 py-2 fw-600" style="border-radius: 12px; border: 1px solid #e2e8f0;" onclick="rejectPrivacy()">Decline</button>
                    <button type="button" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius: 12px;" onclick="acceptPrivacyPolicy()">Accept & Continue</button>
                </div>
            </div>
        </div>

        <!-- Data Protection Modal -->
        <div class="modal fade data-protection-modal" id="dataProtectionModal" tabindex="-1" aria-labelledby="dataProtectionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="dataProtectionModalLabel"><i class="fas fa-shield-alt me-2"></i> Data Protection & Privacy Policy</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <img src="{{ asset('assets/img/logo/logo.png') }}" alt="Logo" style="height: 60px;">
                            <h4 class="mt-3 text-dark fw-800">Baya Jidda Data Privacy Commitment</h4>
                        </div>

                        <p class="lead text-center mb-4" style="font-size: 1.1rem; color: #555;">
                            At <strong>Baya Jidda</strong>, we are committed to protecting your personal data in compliance with the 
                            <strong>Nigeria Data Protection Regulation (NDPR) 2019</strong>.
                        </p>

                        <div class="policy-section">
                            <h5>1. Introduction</h5>
                            <p>This Privacy Policy explains how Baya Jidda collects, uses, and protects your personal information when you use our digital solutions, including our website, mobile applications, and NIN/BVN services.</p>
                        </div>
                        
                        <div class="policy-section">
                            <h5>2. Data Collection</h5>
                            <p>We collect information you provide directly to us, such as when you create an account, request services, or contact customer support.</p>
                        </div>

                        <div class="alert alert-warning mt-4 text-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            By clicking "I Agree & Continue", you acknowledge that you have read and understood this Privacy Policy and agree to our Terms of Service.
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="{{ route('register') }}" class="btn btn-primary px-5 py-2 fw-bold" onclick="acceptPrivacyPolicy()">
                            I Agree & Continue <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link href="https://unpkg.com/aos@2.3.4/dist/aos.css" rel="stylesheet">
        <script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
        <script src="{{ asset('assets/js/landing.js') }}"></script>
    </body>
</html>
