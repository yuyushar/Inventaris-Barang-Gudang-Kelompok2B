<x-layout><x-slot:title>Dashboard Petugas · Gudang 2B</x-slot:title>
    <link rel="stylesheet" href="{{ asset('css/fatih-inventaris.css') }}">
    @php
        $ikon = [
            'masuk' => '<path d="M12 3v12m-4-4 4 4 4-4M4 17v4h16v-4"/>',
            'keluar' => '<path d="M12 17V5m-4 4 4-4 4 4M4 17v4h16v-4"/>',
            'peminjaman' => '<rect x="5" y="5" width="14" height="16" rx="2"/><path d="M9 5V3h6v2M9 11h6m-6 4h6"/>',
            'riwayat' => '<path d="M4 12a8 8 0 1 0 2-5.3M4 4v4h4m4-1v5l3 2"/>',
        ];
        $shortcut = [
            [
                'url' => route('petugas.barang-masuk.index'),
                'judul' => 'Barang masuk',
                'deskripsi' => 'Catat barang yang datang dari supplier',
                'ikon' => 'masuk',
                'warna' => 'green',
                'lencana' => $jumlahMenipis > 0 ? $jumlahMenipis.' perlu restok' : null,
            ],
            [
                'url' => route('petugas.barang-keluar.index'),
                'judul' => 'Barang keluar',
                'deskripsi' => 'Catat barang yang diambil atau dikirim',
                'ikon' => 'keluar',
                'warna' => 'amber',
                'lencana' => null,
            ],
            [
                'url' => route('petugas.peminjaman.index'),
                'judul' => 'Peminjaman',
                'deskripsi' => 'Catat peminjaman dan pengembalian barang',
                'ikon' => 'peminjaman',
                'warna' => 'blue',
                'lencana' => $jumlahPeminjamanAktif > 0 ? $jumlahPeminjamanAktif.' belum kembali' : null,
            ],
            [
                'url' => route('petugas.riwayat-stok.index'),
                'judul' => 'Riwayat stok',
                'deskripsi' => 'Lihat log semua perubahan stok',
                'ikon' => 'riwayat',
                'warna' => 'slate',
                'lencana' => null,
            ],
        ];
        $jenisTransaksi = [
            'masuk' => ['Barang masuk', 'fe-badge-green'],
            'keluar' => ['Barang keluar', 'fe-badge-red'],
            'penyesuaian' => ['Penyesuaian', 'fe-badge-gray'],
            'peminjaman' => ['Peminjaman', 'fe-badge-blue'],
            'pengembalian' => ['Pengembalian', 'fe-badge-amber'],
        ];
        $namaKondisi = [
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak ringan',
            'rusak' => 'Rusak',
        ];
    @endphp
    <div class="fe-page">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">OPERASIONAL / DASHBOARD</div>
                <h1>Dashboard Petugas</h1>
                <p>Halo, {{ auth()->user()->name }}. Mulai catat transaksi atau selesaikan antrean tugas hari ini.</p>
            </div>
            <span class="fe-badge fe-badge-gray">{{ now()->format('d/m/Y H:i') }}</span>
        </div>

        <div class="fe-shortcut-grid">
            @foreach($shortcut as $item)
            <a class="fe-shortcut" href="{{ $item['url'] }}">
                <span class="fe-shortcut-icon fe-shortcut-{{ $item['warna'] }}" aria-hidden="true"><svg viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"
                        stroke-linejoin="round">{!! $ikon[$item['ikon']] !!}</svg></span>
                <span class="fe-shortcut-text">
                    <strong>{{ $item['judul'] }}</strong>
                    <small>{{ $item['deskripsi'] }}</small>
                    @if($item['lencana'])
                    <span class="d-block mt-2"><span class="fe-badge fe-badge-amber">{{ $item['lencana'] }}</span></span>
                    @endif
                </span>
                <span class="fe-shortcut-arrow" aria-hidden="true">→</span>
            </a>
            @endforeach
        </div>

        <div class="fe-stat-grid">
            @foreach($kartu as $item)
            <div class="fe-stat fe-stat-{{ $item['warna'] }}">
                <span class="fe-stat-label">{{ $item['label'] }}</span>
                <strong class="fe-stat-value">{{ number_format($item['nilai'], 0, ',', '.') }}</strong>
                <small class="fe-stat-hint">transaksi · {{ $item['keterangan'] }}</small>
            </div>
            @endforeach
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <section class="fe-card h-100">
                    <div class="fe-card-header">
                        <h2>Antrean: peminjaman belum kembali</h2>
                        <span>{{ number_format($jumlahPeminjamanAktif, 0, ',', '.') }} aktif ·
                            <a class="fe-card-link" href="{{ route('petugas.peminjaman.index') }}">Proses pengembalian</a></span>
                    </div>
                    @if($peminjamanAktif->isEmpty())
                    <p class="fe-empty">Tidak ada peminjaman yang menunggu pengembalian.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table fe-table fe-table-compact align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Peminjam</th>
                                    <th>Jumlah</th>
                                    <th>Lama</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($peminjamanAktif as $peminjaman)
                                @php
                                    $lamaHari = (int) $peminjaman->tanggal_pinjam->copy()->startOfDay()->diffInDays(today());
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fe-cell-title">{{ $peminjaman->barang?->nama_barang ?? '—' }}</div>
                                        <small class="text-muted">{{ $peminjaman->nomor_peminjaman }}</small>
                                    </td>
                                    <td>{{ $peminjaman->peminjam_nama }}</td>
                                    <td>{{ number_format($peminjaman->jumlah, 0, ',', '.') }}
                                        {{ $peminjaman->barang?->satuan }}</td>
                                    <td><span class="fe-badge fe-badge-gray">{{ $lamaHari === 0 ? 'Hari ini' : $lamaHari.' hari' }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </section>
            </div>
            <div class="col-lg-6">
                <section class="fe-card h-100">
                    <div class="fe-card-header">
                        <h2>Antrean: perlu restok</h2>
                        <span>{{ number_format($jumlahMenipis, 0, ',', '.') }} barang ·
                            <a class="fe-card-link" href="{{ route('petugas.barang-masuk.index') }}">Catat barang masuk</a></span>
                    </div>
                    @if($barangMenipis->isEmpty())
                    <p class="fe-empty">Semua stok masih aman, tidak ada yang perlu direstok.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table fe-table fe-table-compact align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Stok baik</th>
                                    <th>Minimum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($barangMenipis as $barang)
                                <tr>
                                    <td>
                                        <div class="fe-cell-title">{{ $barang->nama_barang }}</div>
                                        <small class="text-muted">{{ $barang->kode_barang }}</small>
                                    </td>
                                    <td><span class="fe-badge {{ $barang->stok_baik < 1 ? 'fe-badge-red' : 'fe-badge-amber' }}">{{ number_format($barang->stok_baik, 0, ',', '.') }}
                                            {{ $barang->satuan }}</span></td>
                                    <td>{{ number_format($barang->stok_minimum, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </section>
            </div>
        </div>

        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Aktivitas terakhir saya</h2>
                <span><a class="fe-card-link" href="{{ route('petugas.riwayat-stok.index') }}">Lihat semua</a></span>
            </div>
            @if($aktivitasSaya->isEmpty())
            <p class="fe-empty">Kamu belum mencatat aktivitas apa pun.</p>
            @else
            <div class="table-responsive">
                <table class="table fe-table fe-table-compact align-middle w-100">
                    <thead>
                        <tr>
                            <th>Aktivitas</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($aktivitasSaya as $log)
                        @php
                            $infoJenis = $jenisTransaksi[$log->jenis_transaksi] ?? [ucfirst($log->jenis_transaksi), 'fe-badge-gray'];
                        @endphp
                        <tr>
                            <td><span class="fe-badge {{ $infoJenis[1] }}">{{ $infoJenis[0] }}</span></td>
                            <td>
                                <div class="fe-cell-title">{{ $log->barang?->nama_barang ?? '—' }}</div>
                                <small class="text-muted">{{ $namaKondisi[$log->kondisi] ?? $log->kondisi }}</small>
                            </td>
                            <td>{{ number_format($log->jumlah, 0, ',', '.') }} {{ $log->barang?->satuan }}</td>
                            <td>{{ $log->tanggal?->format('d/m H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>
    </div>
</x-layout>
