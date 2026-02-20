<!-- About Us Section -->
<style>
    .about-section { padding: 120px 0; background: #fdfdfd; position: relative; overflow: hidden; }
    .about-decorative-1 { position: absolute; top: -100px; left: -100px; width: 400px; height: 400px; background: radial-gradient(circle, rgba(0, 47, 186, 0.05) 0%, transparent 70%); border-radius: 50%; }
    .about-decorative-2 { position: absolute; bottom: -150px; right: -50px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(46, 204, 113, 0.03) 0%, transparent 70%); border-radius: 50%; }
    .about-badge { background: #e0e7ff; color: #4338ca; padding: 10px 20px; font-weight: 800; letter-spacing: 2px; }
    .about-title { font-size: 3.5rem; font-weight: 900; line-height: 1.1; margin-bottom: 30px; }
    .feature-icon-small { width: 55px; height: 55px; background: #fff; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #002fba; box-shadow: 0 10px 20px rgba(0,0,0,0.05); transition: all 0.3s ease; }
    .feature-item:hover .feature-icon-small { transform: scale(1.1) rotate(5deg); background: #002fba; color: #fff; }
    .pulse-blue { animation: pulse-blue 2s infinite; }
    @keyframes pulse-blue { 0% { box-shadow: 0 0 0 0 rgba(0, 47, 186, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(0, 47, 186, 0); } 100% { box-shadow: 0 0 0 0 rgba(0, 47, 186, 0); } }
    .about-slider-container { position: relative; padding: 20px; }
    .experience-badge { position: absolute; bottom: -30px; right: -30px; width: 180px; height: 180px; background: #002696; border: 10px solid #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-align: center; color: #fff; z-index: 10; transform: rotate(-10deg); transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .experience-badge:hover { transform: rotate(0deg) scale(1.1); }
    .experience-badge .count { display: block; font-size: 3.5rem; font-weight: 900; line-height: 1; }
    .experience-badge .label { font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
</style>
<section id="about-us" class="about-section">
    <!-- Decorative Elements -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; opacity: 0.4;">
        <div class="about-decorative-1"></div>
        <div class="about-decorative-2"></div>
    </div>

    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Content Column -->
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-header mb-5">
                    <span class="badge rounded-pill mb-3 about-badge">ESTABLISHED 2011</span>
                    <h2 class="about-title mb-4">
                        15 Years of <span class="text-primary">Global Excellence</span> in Digital Solutions
                    </h2>
                    <p class="lead" style="color: #4b5563; font-size: 1.2rem; line-height: 1.8; margin-bottom: 30px;">
                        Founded as <strong>Baya Jidda Global Ventures</strong>, we have spent over a decade and a half revolutionizing the way Africa interacts with global travel and digital commerce.
                    </p>
                </div>

                <div class="about-features mb-5">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="feature-item d-flex align-items-center gap-3">
                                <div class="feature-icon-small pulse-blue"><i class="fas fa-globe-africa"></i></div>
                                <h5 style="margin: 0; font-weight: 700; font-size: 1.1rem;">Global Presence</h5>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="feature-item d-flex align-items-center gap-3">
                                <div class="feature-icon-small pulse-green"><i class="fas fa-chart-line"></i></div>
                                <h5 style="margin: 0; font-weight: 700; font-size: 1.1rem;">Proven Track Record</h5>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="feature-item d-flex align-items-center gap-3">
                                <div class="feature-icon-small pulse-orange"><i class="fas fa-history"></i></div>
                                <h5 style="margin: 0; font-weight: 700; font-size: 1.1rem;">15+ Years Legacy</h5>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="feature-item d-flex align-items-center gap-3">
                                <div class="feature-icon-small pulse-purple"><i class="fas fa-headset"></i></div>
                                <h5 style="margin: 0; font-weight: 700; font-size: 1.1rem;">Expert Support</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-stats d-flex gap-5 mb-5">
                    <div>
                        <h3 style="color: #002fba; font-weight: 800; font-size: 2.2rem; margin-bottom: 5px;">15+</h3>
                        <p style="color: #6b7280; font-weight: 600;">Years of Impact</p>
                    </div>
                    <div style="width: 1px; background: #e5e7eb; height: 50px;"></div>
                    <div>
                        <h3 style="color: #2ecc71; font-weight: 800; font-size: 2.2rem; margin-bottom: 5px;">20+</h3>
                        <p style="color: #6b7280; font-weight: 600;">Global Offices</p>
                    </div>
                    <div style="width: 1px; background: #e5e7eb; height: 50px;"></div>
                    <div>
                        <h3 style="color: #8e44ad; font-weight: 800; font-size: 2.2rem; margin-bottom: 5px;">1M+</h3>
                        <p style="color: #6b7280; font-weight: 600;">Happy Clients</p>
                    </div>
                </div>

                <p style="color: #6b7280; line-height: 1.7; font-size: 1.05rem;">
                    From our headquarters in Kano to our strategic offices around the globe, Baya Jidda Global Ventures remains dedicated to providing seamless travel bookings, hotel reservations, and efficient bill payment systems that bridge the gap between technology and everyday convenience.
                </p>
            </div>

            <!-- Slider Column -->
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-slider-container">
                    <div id="aboutCarousel" class="carousel slide carousel-fade shadow-2xl" data-bs-ride="carousel" style="border-radius: 30px; overflow: hidden; border: 8px solid #fff;">
                        <div class="carousel-inner">
                            <div class="carousel-item active" data-bs-interval="3000">
                                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="d-block w-100" style="height: 550px; object-fit: cover;" alt="Global Office">
                                <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); bottom: 0; left: 0; right: 0; padding: 20px;">
                                    <h5 class="fw-bold">Global Infrastructure</h5>
                                    <p>Our modern hubs span across major global cities.</p>
                                </div>
                            </div>
                            <div class="carousel-item" data-bs-interval="3000">
                                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="d-block w-100" style="height: 550px; object-fit: cover;" alt="Travel Logistics">
                                <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); bottom: 0; left: 0; right: 0; padding: 20px;">
                                    <h5 class="fw-bold">Seamless Travel</h5>
                                    <p>Expert flight booking management for 15 solid years.</p>
                                </div>
                            </div>
                            <div class="carousel-item" data-bs-interval="3000">
                                <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="d-block w-100" style="height: 550px; object-fit: cover;" alt="Luxury Hotels">
                                <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); bottom: 0; left: 0; right: 0; padding: 20px;">
                                    <h5 class="fw-bold">Hospitality Experts</h5>
                                    <p>Connecting you to the finest stays worldwide.</p>
                                </div>
                            </div>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    <!-- Experience Badge -->
                    <div class="experience-badge shadow-lg d-none d-sm-flex">
                        <div class="badge-content">
                            <span class="count">15</span>
                            <span class="label">Years of<br>Trust</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </section>
