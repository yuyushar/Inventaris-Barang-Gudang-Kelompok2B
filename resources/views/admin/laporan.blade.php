<x-layout><x-slot:title>Laporan Gudang · Gudang 2B</x-slot:title>
    @include('admin.partials.assets')
    @php
        $jenisAktif = old('jenis_laporan', $filters['jenis_laporan'] ?? 'stok');
        $tanggalDariAktif = old('tanggal_dari', $filters['tanggal_dari'] ?? '');
        $tanggalSampaiAktif = old('tanggal_sampai', $filters['tanggal_sampai'] ?? '');
        $kolomAngka = ['No', 'Jumlah', 'Stok baik', 'Rusak ringan', 'Rusak', 'Dipinjam', 'Total', 'Stok minimum'];
        $terpotong = count($laporan['baris']) < $laporan['total'];
    @endphp
    <div class="fe-page">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">PELAPORAN / LAPORAN</div>
                <h1>Laporan Gudang</h1>
                <p>Susun laporan stok atau mutasi barang, lalu cetak sebagai PDF atau unduh untuk Excel.</p>
            </div>
            <div class="fe-heading-actions">
                <a class="btn fe-btn-secondary" href="{{ route('admin.laporan.cetak', $parameterUnduh) }}" target="_blank"
                    rel="noopener">Cetak / PDF</a>
                <a class="btn fe-btn-primary" href="{{ route('admin.laporan.export', $parameterUnduh) }}">Unduh Excel</a>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="fe-card mb-3">
            <div class="fe-card-header">
                <h2>Pengaturan laporan</h2>
                <span>Pilih jenis dan filter, lalu tekan Tampilkan</span>
            </div>
            <div class="fe-card-body">
                <form method="GET" action="{{ route('admin.laporan.index') }}" class="row g-3" id="formLaporan">
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="jenis_laporan">Jenis laporan</label>
                        <select id="jenis_laporan" name="jenis_laporan" class="form-select">
                            <option value="stok" @selected($jenisAktif === 'stok')>Stok barang (posisi saat ini)</option>
                            <option value="mutasi" @selected($jenisAktif === 'mutasi')>Mutasi stok (per periode)</option>
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="kategori_id">Kategori</label>
                        <select id="kategori_id" name="kategori_id" class="form-select">
                            <option value="">Semua kategori</option>
                            @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" @selected((string) old('kategori_id', $filters['kategori_id'] ?? '') === (string) $kategori->id)>{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3" data-laporan-field="mutasi">
                        <label class="form-label" for="tanggal_dari">Dari tanggal</label>
                        <input id="tanggal_dari" name="tanggal_dari" type="date" class="form-control"
                            value="{{ $tanggalDariAktif }}">
                    </div>
                    <div class="col-sm-6 col-lg-3" data-laporan-field="mutasi">
                        <label class="form-label" for="tanggal_sampai">Sampai tanggal</label>
                        <input id="tanggal_sampai" name="tanggal_sampai" type="date" class="form-control"
                            value="{{ $tanggalSampaiAktif }}">
                    </div>
                    <div class="col-sm-6 col-lg-3" data-laporan-field="mutasi">
                        <label class="form-label" for="jenis_transaksi">Jenis transaksi</label>
                        <select id="jenis_transaksi" name="jenis_transaksi" class="form-select">
                            <option value="">Semua jenis</option>
                            @foreach($jenisTransaksi as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('jenis_transaksi', $filters['jenis_transaksi'] ?? '') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn fe-btn-primary">Tampilkan</button>
                        <a class="btn fe-btn-secondary" href="{{ route('admin.laporan.index') }}">Reset</a>
                    </div>
                </form>
            </div>
        </section>

        <div class="fe-stat-grid">
            @foreach($laporan['ringkasan'] as $kartu)
            <div class="fe-stat fe-stat-{{ $kartu['warna'] }}">
                <span class="fe-stat-label">{{ $kartu['label'] }}</span>
                <strong class="fe-stat-value">{{ number_format($kartu['nilai'], 0, ',', '.') }}</strong>
            </div>
            @endforeach
        </div>

        <section class="fe-card">
            <div class="fe-card-header">
                <h2>{{ $laporan['judul'] }}</h2>
                <span>{{ $laporan['periode'] }}@foreach($laporan['filter'] as $keterangan) · {{ $keterangan }}@endforeach</span>
            </div>
            @if(count($laporan['baris']) === 0)
            <p class="fe-empty">Tidak ada data untuk laporan ini.</p>
            @else
            <div class="table-responsive">
                <table class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            @foreach($laporan['kolom'] as $kolom)
                            <th @class(['text-end' => in_array($kolom, $kolomAngka, true)])>{{ $kolom }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($laporan['baris'] as $baris)
                        <tr>
                            @foreach($baris as $indeks => $sel)
                            <td @class(['text-end' => in_array($laporan['kolom'][$indeks], $kolomAngka, true), 'fe-keterangan' => $laporan['kolom'][$indeks] === 'Keterangan'])>
                                @if($sel === 'Perlu perhatian')
                                <span class="fe-badge fe-badge-red">{{ $sel }}</span>
                                @elseif($sel === 'Aman')
                                <span class="fe-badge fe-badge-green">{{ $sel }}</span>
                                @elseif(is_int($sel) && $laporan['kolom'][$indeks] !== 'No')
                                {{ number_format($sel, 0, ',', '.') }}
                                @else
                                {{ $sel }}
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
            @if($terpotong)
            <p class="fe-note">Pratinjau menampilkan {{ count($laporan['baris']) }} dari
                {{ number_format($laporan['total'], 0, ',', '.') }} baris. Hasil cetak dan unduhan Excel memuat seluruh
                baris.</p>
            @else
            <p class="fe-note">Tombol <strong>Cetak / PDF</strong> membuka halaman siap cetak: pilih
                <em>Simpan sebagai PDF</em> pada jendela cetak browser. <strong>Unduh Excel</strong> menghasilkan file
                .csv yang langsung terbuka di Microsoft Excel.</p>
            @endif
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pilihanJenis = document.getElementById('jenis_laporan');
            const bidangMutasi = document.querySelectorAll('[data-laporan-field="mutasi"]');

            const aturBidang = () => {
                bidangMutasi.forEach((bidang) => {
                    bidang.hidden = pilihanJenis.value !== 'mutasi';
                });
            };

            pilihanJenis.addEventListener('change', aturBidang);
            aturBidang();
        });
    </script>
</x-layout>
