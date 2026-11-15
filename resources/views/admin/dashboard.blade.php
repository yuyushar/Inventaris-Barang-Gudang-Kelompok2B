<x-layout><x-slot:title>Dashboard Admin · Gudang 2B</x-slot:title>
    @include('admin.partials.assets')
    @php
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
        $totalMasuk = array_sum($grafik['transaksi']['masuk']);
        $totalKeluar = array_sum($grafik['transaksi']['keluar']);
        $totalStok = array_sum(array_column($grafik['kondisi'], 'nilai'));
    @endphp
    <div class="fe-page">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">RINGKASAN / DASHBOARD</div>
                <h1>Dashboard Statistik</h1>
                <p>Halo, {{ auth()->user()->name }}. Pantau kondisi stok, aktivitas gudang, dan peminjaman dalam satu
                    halaman.</p>
            </div>
            <span class="fe-badge fe-badge-gray">Diperbarui {{ now()->format('d/m/Y H:i') }}</span>
        </div>

        <div class="fe-stat-grid">
            @foreach($kartu as $item)
            <div class="fe-stat fe-stat-{{ $item['warna'] }}">
                <span class="fe-stat-label">{{ $item['label'] }}</span>
                <strong class="fe-stat-value">{{ number_format($item['nilai'], 0, ',', '.') }}</strong>
                <small class="fe-stat-hint">{{ $item['keterangan'] }}</small>
            </div>
            @endforeach
        </div>

        <p class="fe-note" data-chart-fallback hidden>Grafik tidak dapat dimuat (periksa koneksi internet). Angka tetap
            tersedia pada kartu, legenda, dan tabel di halaman ini.</p>

        <div class="row g-3 mb-3">
            <div class="col-lg-8">
                <section class="fe-card h-100">
                    <div class="fe-card-header">
                        <h2>Barang masuk &amp; keluar</h2>
                        <span>7 hari terakhir · Masuk {{ number_format($totalMasuk, 0, ',', '.') }} · Keluar
                            {{ number_format($totalKeluar, 0, ',', '.') }} unit</span>
                    </div>
                    <div class="fe-card-body">
                        <div class="fe-chart"><canvas id="chartTransaksi" role="img"
                                aria-label="Grafik batang jumlah barang masuk dan keluar per hari selama 7 hari terakhir"></canvas>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <section class="fe-card h-100">
                    <div class="fe-card-header">
                        <h2>Komposisi stok</h2>
                        <span>{{ number_format($totalStok, 0, ',', '.') }} unit tercatat</span>
                    </div>
                    <div class="fe-card-body">
                        <div class="fe-chart fe-chart-short"><canvas id="chartKondisi" role="img"
                                aria-label="Grafik donat komposisi stok berdasarkan kondisi"></canvas></div>
                        <ul class="fe-legend">
                            @foreach($grafik['kondisi'] as $kondisi)
                            <li><span class="fe-legend-dot" style="background: {{ $kondisi['warna'] }}"></span>{{ $kondisi['label'] }}
                                <strong>{{ number_format($kondisi['nilai'], 0, ',', '.') }}</strong></li>
                            @endforeach
                        </ul>
                    </div>
                </section>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <section class="fe-card h-100">
                    <div class="fe-card-header">
                        <h2>Stok per kategori</h2>
                        <span>Total semua kondisi</span>
                    </div>
                    @if(count($grafik['kategori']['labels']) === 0)
                    <p class="fe-empty">Belum ada kategori tercatat.</p>
                    @else
                    <div class="fe-card-body">
                        <div class="fe-chart fe-chart-short"><canvas id="chartKategori" role="img"
                                aria-label="Grafik batang jumlah stok per kategori"></canvas></div>
                    </div>
                    @endif
                </section>
            </div>
            <div class="col-lg-6">
                <section class="fe-card h-100">
                    <div class="fe-card-header">
                        <h2>Barang perlu perhatian</h2>
                        <span>Stok baik ≤ stok minimum</span>
                    </div>
                    @if($barangMenipis->isEmpty())
                    <p class="fe-empty">Semua stok masih aman.</p>
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

        <div class="row g-3">
            <div class="col-lg-6">
                <section class="fe-card h-100">
                    <div class="fe-card-header">
                        <h2>Peminjaman aktif</h2>
                        <span>Belum dikembalikan</span>
                    </div>
                    @if($peminjamanAktif->isEmpty())
                    <p class="fe-empty">Tidak ada peminjaman aktif.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table fe-table fe-table-compact align-middle w-100">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Peminjam</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($peminjamanAktif as $peminjaman)
                                <tr>
                                    <td>
                                        <div class="fe-cell-title">{{ $peminjaman->barang?->nama_barang ?? '—' }}</div>
                                        <small class="text-muted">{{ $peminjaman->nomor_peminjaman }}</small>
                                    </td>
                                    <td>{{ $peminjaman->peminjam_nama }}</td>
                                    <td>{{ number_format($peminjaman->jumlah, 0, ',', '.') }}
                                        {{ $peminjaman->barang?->satuan }}</td>
                                    <td>{{ $peminjaman->tanggal_pinjam?->format('d/m/Y') }}</td>
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
                        <h2>Aktivitas stok terbaru</h2>
                        <span><a href="{{ route('riwayat-stok.index') }}">Lihat semua</a></span>
                    </div>
                    @if($aktivitasTerbaru->isEmpty())
                    <p class="fe-empty">Belum ada aktivitas stok.</p>
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
                                @foreach($aktivitasTerbaru as $log)
                                @php
                                    $infoJenis = $jenisTransaksi[$log->jenis_transaksi] ?? [ucfirst($log->jenis_transaksi), 'fe-badge-gray'];
                                    $labelJenis = $infoJenis[0];
                                    $kelasJenis = $infoJenis[1];
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fe-badge {{ $kelasJenis }}">{{ $labelJenis }}</span>
                                        <div><small class="text-muted">{{ $log->user?->name ?? 'Sistem' }}</small></div>
                                    </td>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Chart) {
                document.querySelector('[data-chart-fallback]')?.removeAttribute('hidden');
                return;
            }

            const grafik = @json($grafik);
            const grid = { color: '#eef2f7' };
            const ticks = { precision: 0 };

            Chart.defaults.font.family = 'Inter, system-ui, -apple-system, "Segoe UI", sans-serif';
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#64748b';

            new Chart(document.getElementById('chartTransaksi'), {
                type: 'bar',
                data: {
                    labels: grafik.transaksi.labels,
                    datasets: [
                        { label: 'Masuk', data: grafik.transaksi.masuk, backgroundColor: '#2563eb', borderRadius: 4 },
                        { label: 'Keluar', data: grafik.transaksi.keluar, backgroundColor: '#f59e0b', borderRadius: 4 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10 } } },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid, ticks } },
                },
            });

            new Chart(document.getElementById('chartKondisi'), {
                type: 'doughnut',
                data: {
                    labels: grafik.kondisi.map((item) => item.label),
                    datasets: [{
                        data: grafik.kondisi.map((item) => item.nilai),
                        backgroundColor: grafik.kondisi.map((item) => item.warna),
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: { legend: { display: false } },
                },
            });

            const kanvasKategori = document.getElementById('chartKategori');

            if (kanvasKategori) {
                new Chart(kanvasKategori, {
                    type: 'bar',
                    data: {
                        labels: grafik.kategori.labels,
                        datasets: [{ label: 'Total stok', data: grafik.kategori.data, backgroundColor: '#2563eb', borderRadius: 4 }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, grid, ticks }, y: { grid: { display: false } } },
                    },
                });
            }
        });
    </script>
</x-layout>
