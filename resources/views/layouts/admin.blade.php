<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - AlfarFID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    {{-- Alpine.js: WAJIB ada `defer` supaya jalan setelah DOM siap,
         dan HARUS di-load sebelum @stack('scripts') dieksekusi --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>

    <style>
        :root {
            --bg: #F4F5F7;
            --surface: #FFFFFF;
            --ink: #121826;
            --ink-soft: #646E84;
            --line: #E1E4EA;
            --navy: #1B3358;
            --navy-deep: #0E1E38;
            --navy-soft: #EAF0F7;
            --copper: #302ea6;
            --copper-soft: #F5E9E0;
            --teal: #0F766E;
            --teal-soft: #E3F3F1;
            --red: #E63946;
            --red-soft: #FBEAE9;
        }

        /* WAJIB: sembunyikan elemen x-cloak sebelum Alpine selesai init,
           supaya modal/tab tidak "kelihatan sekilas" saat halaman dimuat */
        [x-cloak] {
            display: none !important;
        }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Inter', sans-serif;
        }

        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }

        .font-mono {
            font-family: 'IBM Plex Mono', monospace;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--navy) 0%, var(--navy-deep) 100%);
        }

        .nav-link {
            color: #B9C6DC;
            transition: all .15s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            box-shadow: inset 3px 0 0 var(--copper);
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 14px;
        }

        .gauge-track {
            stroke: #E7E9EE;
        }

        .badge-role {
            background: var(--navy-soft);
            color: var(--navy);
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #CBD2DE;
            border-radius: 8px;
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

<body class="min-h-screen">

    <div class="flex min-h-screen">

        @include('partials.admin.sidebar')

        <div id="overlay" class="fixed inset-0 bg-black/30 z-30 hidden lg:hidden"></div>

        <div class="flex-1 min-w-0">

            @include('partials.admin.topbar')

            <main class="p-4 lg:p-8 space-y-8">
                @yield('content')
            </main>

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
