<x-layout><x-slot:title>Riwayat Stok (Log) · Gudang 2B</x-slot:title>
    @include('admin.partials.assets')
    @php
        $urlHalaman = url()->current();
        $hariIni = today()->toDateString();
        $rentangCepat = [
            'Hari ini' => [$hariIni, $hariIni],
            '7 hari terakhir' => [today()->subDays(6)->toDateString(), $hariIni],
            'Bulan ini' => [today()->startOfMonth()->toDateString(), $hariIni],
        ];
        $tanggalDariAktif = old('tanggal_dari', $filters['tanggal_dari'] ?? '');
        $tanggalSampaiAktif = old('tanggal_sampai', $filters['tanggal_sampai'] ?? '');
        $kelasJenis = [
            'masuk' => 'fe-badge-green',
            'keluar' => 'fe-badge-red',
            'penyesuaian' => 'fe-badge-gray',
            'peminjaman' => 'fe-badge-blue',
            'pengembalian' => 'fe-badge-amber',
        ];
        $namaKondisi = [
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak ringan',
            'rusak' => 'Rusak',
        ];
        $adaFilter = collect($filters)->filter(fn ($nilai) => $nilai !== null && $nilai !== '')->isNotEmpty();
    @endphp
    <div class="fe-page">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">AUDIT / RIWAYAT STOK</div>
                <h1>Riwayat Stok</h1>
                <p>Catatan setiap perubahan stok. Data ini hanya dapat dilihat dan tidak bisa diubah dari halaman ini.</p>
            </div>
        </div>

        @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="fe-card mb-3">
            <div class="fe-card-header">
                <h2>Filter periode &amp; transaksi</h2>
                <span>{{ $adaFilter ? 'Filter aktif' : 'Menampilkan semua catatan' }}</span>
            </div>
            <div class="fe-card-body">
                <div class="fe-chips" aria-label="Rentang cepat">
                    @foreach($rentangCepat as $label => [$dari, $sampai])
                    <a class="fe-chip {{ $tanggalDariAktif === $dari && $tanggalSampaiAktif === $sampai ? 'is-active' : '' }}"
                        href="{{ $urlHalaman }}?{{ http_build_query(array_merge(request()->except(['page', 'tanggal_dari', 'tanggal_sampai']), ['tanggal_dari' => $dari, 'tanggal_sampai' => $sampai])) }}">{{ $label }}</a>
                    @endforeach
                </div>
                <form method="GET" action="{{ $urlHalaman }}" class="row g-3">
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="tanggal_dari">Dari tanggal</label>
                        <input id="tanggal_dari" name="tanggal_dari" type="date" class="form-control"
                            value="{{ $tanggalDariAktif }}">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="tanggal_sampai">Sampai tanggal</label>
                        <input id="tanggal_sampai" name="tanggal_sampai" type="date" class="form-control"
                            value="{{ $tanggalSampaiAktif }}">
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="jenis_transaksi">Jenis transaksi</label>
                        <select id="jenis_transaksi" name="jenis_transaksi" class="form-select">
                            <option value="">Semua jenis</option>
                            @foreach($jenisTransaksi as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('jenis_transaksi', $filters['jenis_transaksi'] ?? '') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="barang_id">Barang</label>
                        <select id="barang_id" name="barang_id" class="form-select">
                            <option value="">Semua barang</option>
                            @foreach($barangs as $barang)
                            <option value="{{ $barang->id }}" @selected((string) old('barang_id', $filters['barang_id'] ?? '') === (string) $barang->id)>{{ $barang->kode_barang }} · {{ $barang->nama_barang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <label class="form-label" for="user_id">Petugas</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">Semua petugas</option>
                            @foreach($users as $pengguna)
                            <option value="{{ $pengguna->id }}" @selected((string) old('user_id', $filters['user_id'] ?? '') === (string) $pengguna->id)>{{ $pengguna->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-5">
                        <label class="form-label" for="cari">Cari</label>
                        <input id="cari" name="cari" type="search" maxlength="100" class="form-control"
                            placeholder="Kode/nama barang, petugas, atau nomor transaksi"
                            value="{{ old('cari', $filters['cari'] ?? '') }}">
                    </div>
                    <div class="col-12 col-lg-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn fe-btn-primary">Terapkan filter</button>
                        @if($adaFilter)
                        <a class="btn fe-btn-secondary" href="{{ $urlHalaman }}">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Log Audit</h2>
                @if($riwayat->total() > 0)
                <span>Menampilkan {{ $riwayat->firstItem() }}–{{ $riwayat->lastItem() }} dari
                    {{ number_format($riwayat->total(), 0, ',', '.') }} catatan</span>
                @else
                <span>0 catatan</span>
                @endif
            </div>
            @if($riwayat->isEmpty())
            <p class="fe-empty">{{ $adaFilter ? 'Tidak ada catatan yang cocok dengan filter ini.' : 'Belum ada catatan perubahan stok.' }}</p>
            @else
            <div class="table-responsive">
                <table class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aktivitas</th>
                            <th>Barang</th>
                            <th>Kondisi</th>
                            <th>Jumlah</th>
                            <th>Petugas</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->tanggal?->format('d/m/Y') }}
                                <div><small class="text-muted">{{ $log->tanggal?->format('H:i') }}</small></div>
                            </td>
                            <td><span class="fe-badge {{ $kelasJenis[$log->jenis_transaksi] ?? 'fe-badge-gray' }}">{{ $jenisTransaksi[$log->jenis_transaksi] ?? ucfirst($log->jenis_transaksi) }}</span></td>
                            <td>
                                <div class="fe-cell-title">{{ $log->barang?->nama_barang ?? '—' }}</div>
                                <small class="text-muted">{{ $log->barang?->kode_barang }}</small>
                            </td>
                            <td>{{ $namaKondisi[$log->kondisi] ?? $log->kondisi }}</td>
                            <td class="text-nowrap">{{ number_format($log->jumlah, 0, ',', '.') }} {{ $log->barang?->satuan }}</td>
                            <td>{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="fe-keterangan">{{ $log->keterangan ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($riwayat->hasPages())
            <div class="fe-pagination">{{ $riwayat->links('pagination::bootstrap-5') }}</div>
            @endif
            @endif
        </section>
    </div>
</x-layout>
