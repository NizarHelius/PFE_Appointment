<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $setting->meta_title }}</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $setting->meta_description }}">
    <meta name="keywords" content="{{ $setting->meta_keywords }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #2e59d9;
            --accent-color: #f8f9fc;
            --dark-color: #2c3e50;
            --light-color: #f8f9fa;
            --success-color: #1cc88a;
            --text-color: #5a5c69;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 5rem 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3QgZmlsbD0idXJsKCNwYXR0ZXJuKSIgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIvPjwvc3ZnPg==');
        }

        .section-title {
            position: relative;
            margin-bottom: 3rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -0.75rem;
            left: 0;
            width: 60px;
            height: 4px;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .section-title.text-center::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .feature-box {
            background-color: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .animate-slide-in {
            animation: slideIn 0.5s ease forwards;
            opacity: 0;
        }

        @keyframes slideIn {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero-section {
                padding: 3rem 0;
            }
        }
    </style>
    @if ($setting->header)
    {!! $setting->header !!}
    @endif
</head>

<body>
    {{-- nav --}}
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">
            <a class="h2 text-decoration-none" href="#">{{ $setting->bname }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                    <ul class="nav">
                        @guest
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="/register">Register</a>
                        </li>
                        @endguest
                        @auth
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/">Home</a>
                        </li>
                        <li class="nav-item ">
                            <a class="nav-link text-dark" href="/dashboard">My Account</a>
                        </li>
                        @endauth
                    </ul>

                    <a class=" btn btn-primary btn-md rounded-1 h3 ms-md-4" aria-current="page"
                        href="#">{{ $setting->phone }}</a>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-5 fw-bold mb-4">Book Appointments Easily</h1>
                    <p class="lead mb-4">Streamline your scheduling process with our intuitive appointment booking system. Save time and focus on what matters most.</p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('home') }}" class="btn btn-light btn-lg px-4" style="pointer-events: auto; cursor: pointer;">Book Now</a>
                        <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="https://illustrations.popsy.co/amber/digital-nomad.svg" alt="Appointment Booking" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Why Choose Us</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3>Time Saving</h3>
                        <p>Reduce no-shows and save administrative time with automated reminders and easy rescheduling.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="bi bi-calendar2-check"></i>
                        </div>
                        <h3>Easy Scheduling</h3>
                        <p>Intuitive interface makes booking appointments simple for both staff and customers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3>Performance Insights</h3>
                        <p>Get valuable data on your business performance with our comprehensive analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">How It Works</h2>
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="pe-lg-5">
                        <div class="d-flex mb-4">
                            <div class="me-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="h4 mb-0">1</span>
                                </div>
                            </div>
                            <div>
                                <h4>Select a Service</h4>
                                <p>Choose from our wide range of professional services tailored to your needs.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="me-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="h4 mb-0">2</span>
                                </div>
                            </div>
                            <div>
                                <h4>Pick a Professional</h4>
                                <p>Select your preferred staff member based on availability and expertise.</p>
                            </div>
                        </div>
                        <div class="d-flex mb-4">
                            <div class="me-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="h4 mb-0">3</span>
                                </div>
                            </div>
                            <div>
                                <h4>Choose Date & Time</h4>
                                <p>View real-time availability and select the most convenient slot for you.</p>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="me-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <span class="h4 mb-0">4</span>
                                </div>
                            </div>
                            <div>
                                <h4>Confirm Booking</h4>
                                <p>Complete your booking and receive instant confirmation with all details.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <img src="https://illustrations.popsy.co/amber/designer.svg" alt="How it works" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Section -->
    <section id="booking" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title text-center mb-5">Make an Appointment</h2>
            <div class="text-center">
                <p class="lead mb-4">Ready to book your appointment? Click the button below to get started.</p>
                <a href="{{ route('home') }}" class="btn btn-primary btn-lg px-4">Book Appointment Now</a>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="section-title text-center mb-5">What Our Clients Say</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="card-text">"This booking system has saved me so much time. The interface is intuitive and the reminders have significantly reduced no-shows."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="https://randomuser.me/api/portraits/women/32.jpg" class="rounded-circle me-3" width="50" height="50" alt="Client">
                                <div>
                                    <h6 class="mb-0">Sarah Johnson</h6>
                                    <small class="text-muted">Small Business Owner</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="card-text">"As a customer, I love how easy it is to book appointments. The real-time availability makes scheduling a breeze."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="https://randomuser.me/api/portraits/men/45.jpg" class="rounded-circle me-3" width="50" height="50" alt="Client">
                                <div>
                                    <h6 class="mb-0">Michael Chen</h6>
                                    <small class="text-muted">Regular Client</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="mb-3 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                            </div>
                            <p class="card-text">"The reporting features give me great insights into my business. I can track peak times and optimize my staff scheduling."</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" class="rounded-circle me-3" width="50" height="50" alt="Client">
                                <div>
                                    <h6 class="mb-0">Emily Rodriguez</h6>
                                    <small class="text-muted">Salon Manager</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Simplify Your Scheduling?</h2>
            <p class="lead mb-4">Join thousands of businesses that trust our appointment booking system.</p>
            <a href="#booking" class="btn btn-light btn-lg px-4 me-2">Book an Appointment</a>
            <a href="#" class="btn btn-outline-light btn-lg px-4">Contact Us</a>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3"><i class="bi bi-calendar-check"></i> {{ $setting->bname }}</h5>
                    <p>Streamline your appointment scheduling process with our easy-to-use booking system.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#features">Features</a></li>
                        <li class="mb-2"><a href="#how-it-works">How It Works</a></li>
                        <li class="mb-2"><a href="#booking">Book Now</a></li>
                        <li class="mb-2"><a href="#">Pricing</a></li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Contact Us</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> {{ $setting->email }}</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> {{ $setting->phone }}</li>
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> {{ $setting->address }}</li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5 class="mb-3">Follow Us</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-2"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4 bg-light">
            <div class="row text-center">
                <div class="col-md-6 text-md-start mb-3 mb-md-0">
                    <span>&copy; {{ date('Y') }} {{ $setting->bname }}. All rights reserved.</span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span>Designed & Developed by <a target="_blank" href="https://www.vfixtechnology.com">VFIX TECHNOLOGY</a></span>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Smooth scrolling for anchor links
            $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
                if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        event.preventDefault();
                        $('html, body').animate({
                            scrollTop: target.offset().top - 70
                        }, 1000, function() {
                            var $target = $(target);
                            $target.focus();
                            if ($target.is(":focus")) {
                                return false;
                            } else {
                                $target.attr('tabindex', '-1');
                                $target.focus();
                            }
                        });
                    }
                }
            });
        });
    </script>

    @if ($setting->footer)
    {!! $setting->footer !!}
    @endif
</body>

</html>