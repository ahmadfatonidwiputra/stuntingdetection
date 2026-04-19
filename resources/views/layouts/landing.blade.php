<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="AI Stunt Detect - Sistem deteksi dini stunting berbasis AI untuk posyandu Indonesia. Pantau pertumbuhan anak dengan teknologi kecerdasan buatan.">

    <title>@yield('title', 'AI Stunt Detect - Deteksi Dini Stunting Berbasis AI')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">

    <!-- Open Graph / WhatsApp Meta Tags -->
    <meta property="og:title" content="AI Stunt Detect - Deteksi Dini Stunting Berbasis AI" />
    <meta property="og:description" content="AI Stunt Detect - Sistem deteksi dini stunting berbasis AI untuk posyandu Indonesia. Pantau pertumbuhan anak dengan teknologi kecerdasan buatan." />
    <meta property="og:image" content="{{ asset('logo.png') }}" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="AI Stunt Detect" />
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            /* Colors */
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary: #8b5cf6;
            --accent: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;

            /* Dark Theme (Default) */
            --bg-main: #0a0e1a;
            --bg-card: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
            --text: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --nav-bg: rgba(10, 14, 26, 0.85);
            --glass-hover: rgba(255, 255, 255, 0.06);
            --glass-hover-border: rgba(255, 255, 255, 0.12);
            --shadow-primary: rgba(0, 0, 0, 0.3);
            --mesh-1: rgba(14, 165, 233, 0.12);
            --mesh-2: rgba(139, 92, 246, 0.1);
            --mesh-3: rgba(16, 185, 129, 0.06);
        }

        [data-theme="light"] {
            /* Light Theme Overrides */
            --bg-main: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(15, 23, 42, 0.1);
            --text: #0f172a;
            --text-secondary: #334155;
            --text-muted: #64748b;
            --nav-bg: rgba(255, 255, 255, 0.85);
            --glass-hover: rgba(15, 23, 42, 0.05);
            --glass-hover-border: rgba(15, 23, 42, 0.15);
            --shadow-primary: rgba(15, 23, 42, 0.05);
            --mesh-1: rgba(14, 165, 233, 0.15);
            --mesh-2: rgba(139, 92, 246, 0.12);
            --mesh-3: rgba(16, 185, 129, 0.08);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-main);
            color: var(--text);
            overflow-x: hidden;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* ── Animated Background ────────────────── */
        .bg-mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(ellipse at 20% 0%, var(--mesh-1) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, var(--mesh-2) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, var(--mesh-3) 0%, transparent 60%);
            z-index: 0;
            pointer-events: none;
            transition: background 0.3s ease;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        /* ── Navbar ─────────────────────────────── */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 16px 0;
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            background: var(--nav-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 10px 0;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text);
        }

        .nav-brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
        }

        .nav-brand-text {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            list-style: none;
        }

        .nav-links a {
            padding: 8px 16px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--text);
            background: var(--glass-hover);
        }

        .nav-btn {
            padding: 10px 24px !important;
            background: linear-gradient(135deg, var(--primary), var(--secondary)) !important;
            color: white !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);
            transition: all 0.3s ease !important;
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            min-width: 180px;
            background: var(--nav-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 8px 0;
            z-index: 101;
            margin-top: 8px;
            /* give some space */
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            display: block;
            padding: 10px 16px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s;
            border-radius: 0;
        }

        .dropdown-content a:hover {
            background: var(--glass-hover);
            color: var(--primary);
        }

        .theme-toggle-btn {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text);
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
            margin-left: 8px;
        }

        .theme-toggle-btn:hover {
            background: var(--glass-hover);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            color: var(--text);
            cursor: pointer;
            padding: 8px;
        }

        .mobile-nav {
            display: none;
            position: fixed;
            top: 70px;
            left: 0;
            right: 0;
            background: var(--nav-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 16px 24px;
            z-index: 99;
            max-height: calc(100vh - 70px);
            overflow-y: auto;
        }

        .mobile-nav.open {
            display: block;
        }

        .mobile-nav a,
        .mobile-dropdown-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 15px;
            border-radius: 8px;
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-family: inherit;
        }

        .mobile-nav a:hover,
        .mobile-dropdown-btn:hover {
            background: var(--glass-hover);
            color: var(--text);
        }

        .mobile-dropdown-content {
            display: none;
            padding-left: 12px;
            margin-top: 4px;
            border-left: 2px solid var(--glass-border);
            margin-left: 16px;
        }
        
        .mobile-dropdown-content.show {
            display: block;
        }

        .mobile-dropdown-btn .chevron {
            transition: transform 0.3s ease;
        }

        .mobile-dropdown-btn.active .chevron {
            transform: rotate(180deg);
        }

        /* ── Sections ───────────────────────────── */
        .section {
            padding: 100px 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 16px;
            background: rgba(14, 165, 233, 0.1);
            color: var(--primary);
            border: 1px solid rgba(14, 165, 233, 0.2);
        }

        .section-title {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .section-subtitle {
            font-size: 16px;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        /* ── Glass Cards ────────────────────────── */
        .glass {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .glass:hover {
            border-color: var(--glass-hover-border);
            box-shadow: 0 8px 30px var(--shadow-primary);
            transform: translateY(-4px);
        }

        /* ── Grid ───────────────────────────────── */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        /* ── Buttons ────────────────────────────── */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(14, 165, 233, 0.3);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(14, 165, 233, 0.4);
        }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 32px;
            border-radius: 12px;
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text);
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-outline:hover {
            background: var(--glass-hover);
            border-color: var(--glass-hover-border);
        }

        /* ── Footer ─────────────────────────────── */
        .footer {
            border-top: 1px solid var(--glass-border);
            padding: 60px 24px 30px;
            margin-top: 80px;
        }

        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }

        .footer-brand {
            max-width: 300px;
        }

        .footer-brand p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.7;
            margin-top: 12px;
        }

        .footer-links h4 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-links a {
            display: block;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            padding: 4px 0;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 40px auto 0;
            padding-top: 24px;
            border-top: 1px solid var(--glass-border);
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }

        /* ── Animations ─────────────────────────── */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── Responsive ─────────────────────────── */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .section-title {
                font-size: 28px;
            }

            .section {
                padding: 60px 20px;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .grid-3 {
                grid-template-columns: repeat(2, 1fr);
            }

            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <div class="bg-mesh"></div>
    <div class="content-wrapper">
        <!-- Navbar -->
        <nav class="navbar" id="navbar">
            <div class="nav-container">
                <a href="{{ route('home') }}" class="nav-brand">
                    <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 40px; width: auto;" />
                    <span class="nav-brand-text">AI Stunt Detect</span>
                </a>

                <ul class="nav-links">
                    <li><a href="{{ route('home') }}"
                            class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a></li>
                    <li><a href="{{ route('tentang-stunting') }}"
                            class="{{ request()->routeIs('tentang-stunting') ? 'active' : '' }}">Tentang Stunting</a>
                    </li>
                    <li><a href="{{ route('layanan') }}"
                            class="{{ request()->routeIs('layanan') ? 'active' : '' }}">Layanan</a></li>
                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <li><a href="{{ route('super-admin.dashboard') }}" class="nav-btn">Dashboard</a></li>
                        @elseif(auth()->user()->isOrangTua())
                            <li><a href="{{ route('orang-tua.dashboard') }}" class="nav-btn">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('dashboard') }}" class="nav-btn">Dashboard</a></li>
                        @endif
                    @else
                        <li><a href="{{ route('login') }}">Masuk</a></li>
                        <li class="dropdown">
                            <a href="#" class="nav-btn">Daftar ▼</a>
                            <div class="dropdown-content">
                                <a href="{{ route('register.petugas') }}">Sebagai Petugas Posyandu</a>
                                <a href="{{ route('register.orang-tua') }}">Sebagai Orang Tua</a>
                            </div>
                        </li>
                    @endauth
                    <li>
                        <button id="themeToggle" class="theme-toggle-btn" aria-label="Toggle Theme">
                            🌙
                        </button>
                    </li>
                </ul>

                <button class="mobile-menu-btn" onclick="document.getElementById('mobileNav').classList.toggle('open')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M3 6h18M3 18h18" />
                    </svg>
                </button>
            </div>
        </nav>

        <div class="mobile-nav" id="mobileNav">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('tentang-stunting') }}">Tentang Stunting</a>
            <a href="{{ route('layanan') }}">Layanan</a>
            @auth
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('super-admin.dashboard') }}">Dashboard</a>
                @elseif(auth()->user()->isOrangTua())
                    <a href="{{ route('orang-tua.dashboard') }}">Dashboard</a>
                @else
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                @endif
            @else
                <a href="{{ route('login') }}">Masuk</a>
                
                <!-- Mobile Dropdown -->
                <div class="mobile-dropdown">
                    <button class="mobile-dropdown-btn" onclick="this.nextElementSibling.classList.toggle('show'); this.classList.toggle('active')">
                        Daftar 
                        <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                    <div class="mobile-dropdown-content">
                        <a href="{{ route('register.petugas') }}">Sebagai Petugas Posyandu</a>
                        <a href="{{ route('register.orang-tua') }}">Sebagai Orang Tua</a>
                    </div>
                </div>
            @endauth
            <!-- Theme Toggle for Mobile is handled automatically by CSS/data-theme, but let's add a mobile button too -->
            <a href="#" id="mobileThemeToggle" onclick="event.preventDefault(); toggleTheme();">Ganti Tema
                (Gelap/Terang)</a>
        </div>

        @yield('content')

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-inner">
                <div class="footer-brand">
                    <a href="{{ route('home') }}" class="nav-brand">
                        <img src="{{ asset('logo.png') }}" alt="Logo" style="height: 40px; width: auto;" />
                        <span class="nav-brand-text">AI Stunt Detect</span>
                    </a>
                    <p>Sistem deteksi dini stunting berbasis kecerdasan buatan untuk mendukung program posyandu di
                        Indonesia.</p>
                </div>
                <div class="footer-links">
                    <h4>Navigasi</h4>
                    <a href="{{ route('home') }}">Beranda</a>
                    <a href="{{ route('tentang-stunting') }}">Tentang Stunting</a>
                    <a href="{{ route('layanan') }}">Layanan Posyandu</a>
                </div>
                <div class="footer-links">
                    <h4>Akses</h4>
                    <a href="{{ route('login') }}">Login Petugas</a>
                    <a href="{{ route('register.petugas') }}">Registrasi Petugas</a>
                </div>
                <div class="footer-links">
                    <h4>Info</h4>
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat & Ketentuan</a>
                    <a href="#">Kontak</a>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; {{ date('Y') }} AI Stunt Detect &mdash; Sistem Deteksi Stunting berbasis AI untuk Posyandu
                Indonesia
            </div>
        </footer>
    </div>

    <script>
        // --- Theme Toggle Logic ---
        const themeToggle = document.getElementById('themeToggle');
        const root = document.documentElement;

        // On load, check saved theme or OS preference
        const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
        root.setAttribute('data-theme', savedTheme);
        updateThemeIcon(savedTheme);

        function toggleTheme() {
            const currentTheme = root.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            if (themeToggle) {
                themeToggle.innerHTML = theme === 'dark' ? '☀️' : '🌙';
            }
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }
        // --------------------------

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
        });

        // Fade-in on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    </script>
    @stack('scripts')
</body>

</html>