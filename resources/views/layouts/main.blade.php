<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BodyTrack') }} - Pengukuran Tubuh</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

        <style>
            *, *::before, *::after {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            :root {
                --bg-primary: #0a0e1a;
                --bg-secondary: #111827;
                --bg-card: rgba(17, 24, 39, 0.8);
                --bg-glass: rgba(255, 255, 255, 0.05);
                --border-glass: rgba(255, 255, 255, 0.1);
                --text-primary: #f1f5f9;
                --text-secondary: #94a3b8;
                --text-muted: #64748b;
                --accent-blue: #3b82f6;
                --accent-purple: #8b5cf6;
                --accent-green: #10b981;
                --accent-orange: #f59e0b;
                --accent-red: #ef4444;
                --accent-pink: #ec4899;
                --gradient-1: linear-gradient(135deg, #3b82f6, #8b5cf6);
                --gradient-2: linear-gradient(135deg, #10b981, #3b82f6);
                --gradient-3: linear-gradient(135deg, #f59e0b, #ef4444);
                --gradient-4: linear-gradient(135deg, #ec4899, #8b5cf6);
                --shadow-glow: 0 0 30px rgba(59, 130, 246, 0.15);
                --radius: 16px;
                --radius-sm: 10px;
                --sidebar-width: 260px;
            }

            body {
                font-family: 'Inter', sans-serif;
                background: var(--bg-primary);
                color: var(--text-primary);
                min-height: 100vh;
                overflow-x: hidden;
            }

            /* Background effects */
            body::before {
                content: '';
                position: fixed;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                            radial-gradient(circle at 80% 80%, rgba(139, 92, 246, 0.08) 0%, transparent 50%),
                            radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.04) 0%, transparent 50%);
                z-index: 0;
                pointer-events: none;
            }

            /* Layout */
            .app-layout {
                display: flex;
                min-height: 100vh;
                position: relative;
                z-index: 1;
            }

            /* Sidebar */
            .sidebar {
                width: var(--sidebar-width);
                background: var(--bg-card);
                backdrop-filter: blur(20px);
                border-right: 1px solid var(--border-glass);
                padding: 24px 16px;
                display: flex;
                flex-direction: column;
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 100;
                transition: transform 0.3s ease;
            }

            .sidebar-brand {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 8px 12px;
                margin-bottom: 32px;
            }

            .sidebar-brand-icon {
                width: 42px;
                height: 42px;
                background: var(--gradient-1);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            }

            .sidebar-brand-text {
                font-size: 20px;
                font-weight: 700;
                background: var(--gradient-1);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .sidebar-nav {
                display: flex;
                flex-direction: column;
                gap: 4px;
                flex: 1;
            }

            .sidebar-label {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 1.5px;
                color: var(--text-muted);
                padding: 16px 12px 8px;
                font-weight: 600;
            }

            .nav-link {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 16px;
                border-radius: var(--radius-sm);
                color: var(--text-secondary);
                text-decoration: none;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s ease;
                position: relative;
            }

            .nav-link:hover {
                background: var(--bg-glass);
                color: var(--text-primary);
                transform: translateX(4px);
            }

            .nav-link.active {
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(139, 92, 246, 0.15));
                color: var(--accent-blue);
                border: 1px solid rgba(59, 130, 246, 0.2);
            }

            .nav-link.active::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 3px;
                height: 60%;
                background: var(--gradient-1);
                border-radius: 0 3px 3px 0;
            }

            .nav-icon {
                width: 20px;
                height: 20px;
                flex-shrink: 0;
            }

            .sidebar-footer {
                padding-top: 16px;
                border-top: 1px solid var(--border-glass);
            }

            .user-info {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                border-radius: var(--radius-sm);
                background: var(--bg-glass);
            }

            .user-avatar {
                width: 36px;
                height: 36px;
                border-radius: 10px;
                background: var(--gradient-4);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 14px;
                flex-shrink: 0;
            }

            .user-details {
                flex: 1;
                min-width: 0;
            }

            .user-name {
                font-size: 13px;
                font-weight: 600;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .user-email {
                font-size: 11px;
                color: var(--text-muted);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* Main Content */
            .main-content {
                flex: 1;
                margin-left: var(--sidebar-width);
                padding: 32px;
                min-height: 100vh;
            }

            .page-header {
                margin-bottom: 32px;
            }

            .page-title {
                font-size: 28px;
                font-weight: 800;
                margin-bottom: 4px;
                background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .page-subtitle {
                font-size: 14px;
                color: var(--text-muted);
            }

            /* Cards */
            .glass-card {
                background: var(--bg-card);
                backdrop-filter: blur(20px);
                border: 1px solid var(--border-glass);
                border-radius: var(--radius);
                padding: 24px;
                transition: all 0.3s ease;
            }

            .glass-card:hover {
                border-color: rgba(255, 255, 255, 0.15);
                box-shadow: var(--shadow-glow);
            }

            /* Stats Grid */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 32px;
            }

            .stat-card {
                position: relative;
                overflow: hidden;
            }

            .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                border-radius: var(--radius) var(--radius) 0 0;
            }

            .stat-card.blue::before { background: var(--gradient-1); }
            .stat-card.green::before { background: var(--gradient-2); }
            .stat-card.orange::before { background: var(--gradient-3); }
            .stat-card.purple::before { background: var(--gradient-4); }

            .stat-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 16px;
                font-size: 22px;
            }

            .stat-icon.blue { background: rgba(59, 130, 246, 0.15); color: var(--accent-blue); }
            .stat-icon.green { background: rgba(16, 185, 129, 0.15); color: var(--accent-green); }
            .stat-icon.orange { background: rgba(245, 158, 11, 0.15); color: var(--accent-orange); }
            .stat-icon.purple { background: rgba(139, 92, 246, 0.15); color: var(--accent-purple); }

            .stat-value {
                font-size: 28px;
                font-weight: 800;
                margin-bottom: 4px;
            }

            .stat-label {
                font-size: 13px;
                color: var(--text-muted);
                font-weight: 500;
            }

            /* Charts */
            .chart-container {
                display: grid;
                grid-template-columns: 2fr 1fr;
                gap: 20px;
                margin-bottom: 32px;
            }

            .chart-title {
                font-size: 16px;
                font-weight: 700;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            /* Tables */
            .data-table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
            }

            .data-table th {
                text-align: left;
                padding: 12px 16px;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: var(--text-muted);
                font-weight: 600;
                border-bottom: 1px solid var(--border-glass);
            }

            .data-table td {
                padding: 14px 16px;
                font-size: 14px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.04);
                vertical-align: middle;
            }

            .data-table tr:hover td {
                background: rgba(255, 255, 255, 0.02);
            }

            .data-table tr:last-child td {
                border-bottom: none;
            }

            /* Badges */
            .badge {
                display: inline-flex;
                align-items: center;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 600;
            }

            .badge-kurus { background: rgba(59, 130, 246, 0.15); color: var(--accent-blue); }
            .badge-normal { background: rgba(16, 185, 129, 0.15); color: var(--accent-green); }
            .badge-gemuk { background: rgba(245, 158, 11, 0.15); color: var(--accent-orange); }
            .badge-obesitas { background: rgba(239, 68, 68, 0.15); color: var(--accent-red); }

            /* Buttons */
            .btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 20px;
                border-radius: var(--radius-sm);
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                border: none;
                cursor: pointer;
                transition: all 0.2s ease;
                font-family: 'Inter', sans-serif;
            }

            .btn-primary {
                background: var(--gradient-1);
                color: white;
                box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
            }

            .btn-secondary {
                background: var(--bg-glass);
                color: var(--text-secondary);
                border: 1px solid var(--border-glass);
            }

            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1);
                color: var(--text-primary);
            }

            .btn-danger {
                background: rgba(239, 68, 68, 0.15);
                color: var(--accent-red);
                border: 1px solid rgba(239, 68, 68, 0.3);
            }

            .btn-danger:hover {
                background: rgba(239, 68, 68, 0.25);
            }

            .btn-sm {
                padding: 6px 14px;
                font-size: 12px;
            }

            /* Forms */
            .form-group {
                margin-bottom: 20px;
            }

            .form-label {
                display: block;
                font-size: 13px;
                font-weight: 600;
                color: var(--text-secondary);
                margin-bottom: 8px;
            }

            .form-input, .form-textarea {
                width: 100%;
                padding: 12px 16px;
                background: var(--bg-glass);
                border: 1px solid var(--border-glass);
                border-radius: var(--radius-sm);
                color: var(--text-primary);
                font-size: 14px;
                font-family: 'Inter', sans-serif;
                transition: all 0.2s ease;
                outline: none;
            }

            .form-input:focus, .form-textarea:focus {
                border-color: var(--accent-blue);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
            }

            .form-textarea {
                min-height: 100px;
                resize: vertical;
            }

            .form-error {
                color: var(--accent-red);
                font-size: 12px;
                margin-top: 4px;
            }

            /* Alert Messages */
            .alert {
                padding: 14px 20px;
                border-radius: var(--radius-sm);
                margin-bottom: 20px;
                font-size: 14px;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideDown 0.3s ease;
            }

            .alert-success {
                background: rgba(16, 185, 129, 0.15);
                border: 1px solid rgba(16, 185, 129, 0.3);
                color: var(--accent-green);
            }

            @keyframes slideDown {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Pagination */
            .pagination {
                display: flex;
                gap: 6px;
                justify-content: center;
                margin-top: 24px;
            }

            .pagination a, .pagination span {
                padding: 8px 14px;
                border-radius: 8px;
                font-size: 13px;
                text-decoration: none;
                transition: all 0.2s ease;
            }

            .pagination a {
                background: var(--bg-glass);
                color: var(--text-secondary);
                border: 1px solid var(--border-glass);
            }

            .pagination a:hover {
                background: rgba(59, 130, 246, 0.15);
                color: var(--accent-blue);
            }

            .pagination .active span {
                background: var(--gradient-1);
                color: white;
            }

            .pagination .disabled span {
                color: var(--text-muted);
                opacity: 0.5;
            }

            /* Empty State */
            .empty-state {
                text-align: center;
                padding: 60px 20px;
                color: var(--text-muted);
            }

            .empty-state-icon {
                font-size: 48px;
                margin-bottom: 16px;
            }

            .empty-state h3 {
                font-size: 18px;
                font-weight: 600;
                color: var(--text-secondary);
                margin-bottom: 8px;
            }

            .empty-state p {
                font-size: 14px;
                margin-bottom: 20px;
            }

            /* Camera */
            .camera-container {
                position: relative;
                border-radius: var(--radius);
                overflow: hidden;
                background: #000;
                aspect-ratio: 4/3;
                max-width: 640px;
            }

            .camera-container video,
            .camera-container canvas,
            .camera-container img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .camera-controls {
                display: flex;
                gap: 12px;
                margin-top: 16px;
                flex-wrap: wrap;
            }

            .camera-btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 12px 24px;
                border-radius: 50px;
                font-size: 14px;
                font-weight: 600;
                border: none;
                cursor: pointer;
                transition: all 0.3s ease;
                font-family: 'Inter', sans-serif;
            }

            .camera-btn-capture {
                background: var(--gradient-1);
                color: white;
                box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            }

            .camera-btn-capture:hover {
                transform: scale(1.05);
                box-shadow: 0 6px 25px rgba(59, 130, 246, 0.5);
            }

            .camera-btn-reset {
                background: var(--bg-glass);
                color: var(--text-secondary);
                border: 1px solid var(--border-glass);
            }

            /* Measurement result */
            .measurement-result {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
                margin-top: 20px;
            }

            .result-card {
                flex: 1;
                min-width: 180px;
                padding: 20px;
                background: var(--bg-glass);
                border: 1px solid var(--border-glass);
                border-radius: var(--radius-sm);
                text-align: center;
            }

            .result-value {
                font-size: 32px;
                font-weight: 800;
                background: var(--gradient-1);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .result-unit {
                font-size: 14px;
                color: var(--text-muted);
            }

            /* Grid helpers */
            .grid-2 {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .flex-between {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            /* Mobile Hamburger */
            .mobile-toggle {
                display: none;
                position: fixed;
                top: 16px;
                left: 16px;
                z-index: 200;
                width: 44px;
                height: 44px;
                border-radius: 12px;
                background: var(--bg-card);
                border: 1px solid var(--border-glass);
                color: var(--text-primary);
                cursor: pointer;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                backdrop-filter: blur(20px);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 90;
            }

            /* Detail page */
            .detail-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 32px;
            }

            .detail-photo {
                border-radius: var(--radius);
                overflow: hidden;
                background: #000;
            }

            .detail-photo img {
                width: 100%;
                height: auto;
                display: block;
            }

            .detail-info {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }

            .detail-row {
                display: flex;
                justify-content: space-between;
                padding: 14px 0;
                border-bottom: 1px solid var(--border-glass);
            }

            .detail-row-label {
                color: var(--text-muted);
                font-size: 14px;
            }

            .detail-row-value {
                font-weight: 600;
                font-size: 14px;
            }

            /* Loading spinner */
            .spinner {
                width: 40px;
                height: 40px;
                border: 3px solid var(--border-glass);
                border-top-color: var(--accent-blue);
                border-radius: 50%;
                animation: spin 0.8s linear infinite;
            }

            @keyframes spin {
                to { transform: rotate(360deg); }
            }

            .loading-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                gap: 12px;
                border-radius: var(--radius);
                z-index: 10;
            }

            .loading-text {
                color: var(--text-secondary);
                font-size: 13px;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                }

                .sidebar.open {
                    transform: translateX(0);
                }

                .mobile-toggle {
                    display: flex;
                }

                .sidebar-overlay.active {
                    display: block;
                }

                .main-content {
                    margin-left: 0;
                    padding: 24px 16px;
                    padding-top: 72px;
                }

                .stats-grid {
                    grid-template-columns: 1fr 1fr;
                }

                .chart-container {
                    grid-template-columns: 1fr;
                }

                .grid-2 {
                    grid-template-columns: 1fr;
                }

                .detail-grid {
                    grid-template-columns: 1fr;
                }

                .data-table {
                    font-size: 12px;
                }

                .data-table th, .data-table td {
                    padding: 10px 8px;
                }
            }

            @media (max-width: 480px) {
                .stats-grid {
                    grid-template-columns: 1fr;
                }

                .page-title {
                    font-size: 22px;
                }
            }

            /* Fade-in animation */
            .fade-in {
                animation: fadeIn 0.5s ease forwards;
                opacity: 0;
            }

            @keyframes fadeIn {
                to { opacity: 1; }
            }

            .fade-in:nth-child(1) { animation-delay: 0.05s; }
            .fade-in:nth-child(2) { animation-delay: 0.1s; }
            .fade-in:nth-child(3) { animation-delay: 0.15s; }
            .fade-in:nth-child(4) { animation-delay: 0.2s; }
        </style>

        @stack('styles')
    </head>
    <body>
        <!-- Mobile Toggle -->
        <button class="mobile-toggle" onclick="toggleSidebar()" id="mobile-toggle">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12h18M3 6h18M3 18h18"/>
            </svg>
        </button>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar()"></div>

        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-brand-icon">📏</div>
                    <span class="sidebar-brand-text">BodyTrack</span>
                </div>

                <nav class="sidebar-nav">
                    <span class="sidebar-label">Menu</span>

                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                        Dashboard
                    </a>

                    <a href="{{ route('measurements.create') }}" class="nav-link {{ request()->routeIs('measurements.create') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        Pengukuran Baru
                    </a>

                    <a href="{{ route('measurements.index') }}" class="nav-link {{ request()->routeIs('measurements.index') || request()->routeIs('measurements.show') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        Riwayat Pengukuran
                    </a>

                    <span class="sidebar-label">Akun</span>

                    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profil
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="nav-link" onclick="event.preventDefault(); this.closest('form').submit();">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            Keluar
                        </a>
                    </form>
                </nav>

                <div class="sidebar-footer">
                    <div class="user-info">
                        <div class="user-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        <div class="user-details">
                            <div class="user-name">{{ Auth::user()->name }}</div>
                            <div class="user-email">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <script>
            function toggleSidebar() {
                document.getElementById('sidebar').classList.toggle('open');
                document.getElementById('sidebar-overlay').classList.toggle('active');
            }
        </script>

        @stack('scripts')
    </body>
</html>
