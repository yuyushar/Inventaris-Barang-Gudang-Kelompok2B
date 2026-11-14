<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#192432">
    <title>{{ $title ?? 'Inventaris Gudang' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/gudang-shell.css') }}" rel="stylesheet">
</head>

<body class="g2b-app">
    <x-navbar>{{ $menu ?? '' }}</x-navbar>
    <div class="g2b-backdrop" data-sidebar-close aria-hidden="true"></div>
    <div class="g2b-content">
        <header class="g2b-mobile-header">
            <button type="button" class="g2b-menu-toggle" data-sidebar-toggle aria-label="Buka navigasi"
                aria-controls="g2bSidebar" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
            <span>Gudang <strong>2B</strong></span>
            <span class="g2b-mobile-role">{{ auth()->user()->role === 'admin' ? 'Admin' : 'Petugas' }}</span>
        </header>
        <main id="main-content" class="g2b-main">
            {{ $slot }}
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/gudang-shell.js') }}" defer></script>
</body>

</html>