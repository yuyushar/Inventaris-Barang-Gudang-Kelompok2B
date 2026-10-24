<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark shadow" style="width: 260px; min-height: 100vh;">
    <a href="/" class="d-flex align-items-center mb-4 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold">📦 Gudang 2B</span>
    </a>
    <hr class="mt-0">
    
    <!-- Daftar Menu -->
    <nav class="nav flex-column mb-auto custom-sidebar-nav">
        @if(Auth::user()->role === 'admin')
            <a href="/admin/user">👥 Kelola User</a>
            <a href="/admin/supplier">🏢 Kelola Supplier</a>
            <a href="/admin/kategori">📑 Kelola Kategori</a>
            <a href="/admin/barang">📦 Data Induk Barang</a>
            <a href="/admin/laporan">📊 Laporan Gudang</a>
        @else
            <a href="/petugas/barang-masuk">📥 Barang Masuk</a>
            <a href="/petugas/barang-keluar">📤 Barang Keluar</a>
            <a href="/petugas/peminjaman">🤝 Transaksi Peminjaman</a>
            <a href="/petugas/riwayat-stok">📜 Riwayat Stok (Log)</a>
        @endif
    </nav>
    
    <hr>
    
    <!-- Bagian Profil & Logout -->
    <div class="mt-2">
        <div class="mb-3 text-center">
            <small class="text-muted d-block">Login sebagai:</small>
            <strong>👤 {{ Auth::user()->name }}</strong><br>
            <span class="badge bg-secondary mt-1">{{ strtoupper(Auth::user()->role) }}</span>
        </div>
        <form action="/logout" method="POST" class="m-0">
            @csrf
            <button type="submit" class="btn btn-danger w-100">Logout</button>
        </form>
    </div>
</div>

<style>
    .custom-sidebar-nav a { 
        color: rgba(255,255,255,0.8); 
        text-decoration: none; 
        padding: 10px 15px; 
        margin-bottom: 5px; 
        border-radius: 5px; 
        display: block;
        transition: 0.2s;
    }
    .custom-sidebar-nav a:hover { 
        background-color: rgba(255,255,255,0.1); 
        color: white; 
        padding-left: 20px; 
    }
</style>