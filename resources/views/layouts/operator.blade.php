<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Operator Workstation') - AlfarFID Smart Cleanroom</title>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#EFF6FF',
                            100: '#DBEAFE',
                            200: '#BFDBFE',
                            500: '#3B82F6',
                            600: '#2563EB',
                            700: '#1D4ED8',
                            800: '#1E40AF',
                            900: '#1E3A8A',
                        },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
                        display: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', '"IBM Plex Mono"', 'monospace'],
                    },
                    boxShadow: {
                        'soft': '0 2px 15px -3px rgba(30, 64, 175, 0.05), 0 4px 6px -2px rgba(30, 64, 175, 0.02)',
                        'card': '0 1px 3px 0 rgba(30, 58, 138, 0.06), 0 1px 2px -1px rgba(30, 58, 138, 0.04)',
                        'card-hover': '0 10px 25px -5px rgba(37, 99, 235, 0.12), 0 8px 10px -6px rgba(37, 99, 235, 0.06)',
                    }
                }
            }
        }
    </script>

    {{-- Google Fonts: Plus Jakarta Sans & JetBrains Mono --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        :root {
            --bg: #EEF4FA;
            --surface: #FFFFFF;
            --ink: #0F172A;
            --ink-soft: #64748B;
            --line: #E2E8F0;
        }

        [x-cloak] {
            display: none !important;
        }

        body {
            background-color: #EEF4FA;
            color: #0F172A;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .font-display { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Royal Blue Gradient Sidebar */
        .sidebar-ocean {
            background: linear-gradient(180deg, #1E40AF 0%, #1D4ED8 60%, #1E3A8A 100%);
        }

        .nav-link {
            color: #DBEAFE;
            transition: all .15s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            transform: translateX(2px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: #FFFFFF;
            font-weight: 700;
            border-left: 3.5px solid #38BDF8;
            box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.15);
        }

        .card {
            background-color: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgba(30, 58, 138, 0.05);
            transition: all 0.2s ease;
        }

        .card-hover:hover {
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.1), 0 8px 10px -6px rgba(37, 99, 235, 0.05);
            transform: translateY(-2px);
            border-color: #BFDBFE;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #93C5FD;
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #60A5FA;
        }

        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>

    @stack('styles')
</head>

<body class="min-h-screen bg-[#EEF4FA] text-slate-900 flex flex-col font-sans">

    <div class="flex min-h-screen w-full relative">

        {{-- Sidebar Operator (Royal Blue Theme - Sticky Full Height) --}}
        @include('partials.operator.sidebaroperator')

        {{-- Mobile Overlay --}}
        <div id="overlay" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-30 hidden lg:hidden transition-opacity duration-300"></div>

        {{-- Main Container --}}
        <div class="flex-1 min-w-0 flex flex-col bg-[#EEF4FA]">

            {{-- Topbar --}}
            @include('partials.operator.topbaroperator')

            {{-- Page Content with Balanced Edge Padding --}}
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 w-full space-y-6">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="py-4 px-4 sm:px-6 lg:px-8 border-t border-blue-100 text-xs text-slate-500 font-mono flex flex-col sm:flex-row items-center justify-between gap-2 bg-white/70 backdrop-blur-xs">
                <span>AlfarFID &copy; {{ date('Y') }} &middot; Operator Workstation</span>
                <span class="text-slate-400">Multi-Facility Smart Cleanroom Management</span>
            </footer>

        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const toggle = document.getElementById('sidebarToggle');

        toggle?.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
        });
        overlay?.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.add('hidden');
        });
    </script>

    @stack('scripts')
</body>

</html>
