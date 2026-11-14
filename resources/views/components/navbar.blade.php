@php
    $isAdmin = auth()->user()->role === 'admin';
    $dashboardUrl = $isAdmin ? '/admin/dashboard' : '/petugas/dashboard';
    $items = $isAdmin ? [
        ['/admin/dashboard', 'Dashboard', 'home'],
        ['/admin/user', 'Kelola User', 'users'],
        ['/admin/supplier', 'Kelola Supplier', 'truck'],
        ['/admin/kategori', 'Kelola Kategori', 'grid'],
        ['/admin/barang', 'Data Induk Barang', 'box'],
        ['/admin/laporan', 'Laporan Gudang', 'chart'],
    ] : [
        ['/petugas/dashboard', 'Dashboard', 'home'],
        ['/petugas/barang-masuk', 'Barang Masuk', 'download'],
        ['/petugas/barang-keluar', 'Barang Keluar', 'upload'],
        ['/petugas/peminjaman', 'Transaksi Peminjaman', 'clipboard'],
        ['/petugas/riwayat-stok', 'Riwayat Stok (Log)', 'history'],
    ];
    $icons = [
        'home' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 14h3v4H8zm0-6h8M8 11h8M14 14h2v4h-2"/>',
        'users' => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19v-2a5.5 5.5 0 0 1 11 0v2M17 5a3 3 0 0 1 0 6m1 3a4.5 4.5 0 0 1 2.5 4"/>',
        'truck' => '<path d="M3 6h11v11H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="1.7"/><circle cx="18" cy="18" r="1.7"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.4"/><rect x="14" y="3" width="7" height="7" rx="1.4"/><rect x="3" y="14" width="7" height="7" rx="1.4"/><rect x="14" y="14" width="7" height="7" rx="1.4"/>',
        'box' => '<path d="m3 7 9-4 9 4-9 4-9-4zm0 0v10l9 4 9-4V7M12 11v10"/>',
        'chart' => '<path d="M4 20h16M7 16v-5m5 5V5m5 11V9" stroke-linecap="round"/>',
        'download' => '<path d="M12 3v12m-4-4 4 4 4-4M4 17v4h16v-4"/>',
        'upload' => '<path d="M12 17V5m-4 4 4-4 4 4M4 17v4h16v-4"/>',
        'clipboard' => '<rect x="5" y="5" width="14" height="16" rx="2"/><path d="M9 5V3h6v2M9 11h6m-6 4h6"/>',
        'history' => '<path d="M4 12a8 8 0 1 0 2-5.3M4 4v4h4m4-1v5l3 2"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.8 9a2.4 2.4 0 0 1 4.5 1.2c0 1.5-2.3 2-2.3 3.5M12 17.7h.01"/>',
        'logout' => '<path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5m4-4 4-4-4-4m4 4H9"/>',
    ];
@endphp
<aside id="g2bSidebar" class="g2b-sidebar" aria-label="Navigasi utama">
    <div class="g2b-sidebar-top">
        <a class="g2b-brand" href="{{ $dashboardUrl }}" aria-label="Gudang 2B — ke dashboard">
            <span class="g2b-brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linejoin="round">
                    <path d="m3 7 9-4 9 4-9 4-9-4zM3 7v10l9 4 9-4V7M12 11v10" />
                </svg></span>
            <span class="g2b-brand-name">Gudang <strong>2B</strong><small>Sistem inventaris</small></span>
        </a>
        <button type="button" class="g2b-sidebar-close" data-sidebar-close aria-label="Tutup menu"><svg
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 5l14 14M19 5 5 19" />
            </svg></button>
    </div>
    <div class="g2b-sidebar-scroll">
        <p class="g2b-nav-label">MENU UTAMA</p>
        <nav class="g2b-nav" aria-label="Menu {{ $isAdmin ? 'admin' : 'petugas' }}">
            @foreach($items as [$url, $label, $icon])
            @php($active = request()->is(ltrim($url, '/')))
            <a class="g2b-nav-link {{ $active ? 'is-active' : '' }}" href="{{ $url }}" @if($active) aria-current="page"
            @endif>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">{!! $icons[$icon] !!}</svg>
                <span>{{ $label }}</span>
            </a>
            @endforeach
        </nav>
        <div class="g2b-nav-separator"></div>
        <p class="g2b-nav-label">BANTUAN</p>
        <a class="g2b-nav-link {{ request()->is('panduan') ? 'is-active' : '' }}" href="/panduan" target="_blank"
            rel="noopener noreferrer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"
                aria-hidden="true">{!! $icons['help'] !!}</svg><span>Panduan Penggunaan</span>
            <span class="g2b-external" aria-hidden="true">↗</span>
        </a>
    </div>
    <div class="g2b-sidebar-bottom">
        <div class="g2b-user">
            <span class="g2b-avatar"
                aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="g2b-user-text"><strong
                    title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</strong><small>{{ $isAdmin ? 'Administrator' : 'Petugas gudang' }}</small>
            </div>
        </div>
        <form action="/logout" method="POST" class="m-0">@csrf
            <button type="submit" class="g2b-logout"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.7" stroke-linecap="round"
                    aria-hidden="true">{!! $icons['logout'] !!}</svg><span>Keluar akun</span></button>
        </form>
    </div>
</aside>