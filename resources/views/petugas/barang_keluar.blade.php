<x-layout><x-slot:title>Barang Keluar · Gudang 2B</x-slot:title>
    {{-- FE 2 (Estu) · No. 20: Views & slicing form transaksi barang keluar (approval & pengeluaran stok) --}}
    @include('petugas.partials.transaksi-assets')
    @php
        $waktuSekarang = now()->format('Y-m-d\TH:i');
    @endphp
    <div class="fe-page tx-page" data-tx-server-now="{{ now()->getTimestampMs() }}"
        data-tx-tz-offset="{{ now()->utcOffset() }}">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">TRANSAKSI / BARANG KELUAR</div>
                <h1>Barang Keluar</h1>
                <p>Catat barang yang diambil atau dikirim. Setiap pengeluaran ditinjau dan disetujui sebelum stok
                    dikurangi.</p>
            </div>
            <a class="btn fe-btn-secondary" href="{{ route('petugas.riwayat-stok.index') }}">Lihat riwayat stok</a>
        </div>

        @if($errors->any())
        <div class="tx-alert tx-alert-danger" role="alert">
            <strong>Pengeluaran belum tersimpan.</strong>
            <ul>
                @foreach(array_unique($errors->all()) as $pesan)
                <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($barangs->isEmpty())
        <div class="tx-alert tx-alert-warning" role="status">
            <strong>Tidak ada barang yang bisa dikeluarkan.</strong> Semua barang sedang kosong stok baiknya.
            Catat barang masuk terlebih dahulu.
        </div>
        @endif

        <section class="fe-card mb-3">
            <div class="fe-card-header">
                <h2>Form pengeluaran stok</h2>
                <span>Hanya barang dengan stok baik yang ditampilkan</span>
            </div>
            <form method="POST" action="{{ route('petugas.barang-keluar.store') }}" class="tx-form"
                data-tx-form="keluar" data-tx-approval="#txApprovalModal">
                @csrf
                <div class="fe-card-body">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-12" data-tx-field>
                                    <label class="form-label" for="barang_id">Barang <span
                                            class="fe-required">*</span></label>
                                    <select id="barang_id" name="barang_id"
                                        class="form-select @error('barang_id') is-invalid @enderror" data-tx-barang
                                        data-label="Barang" required>
                                        <option value="">Pilih barang</option>
                                        @foreach($barangs as $barang)
                                        <option value="{{ $barang->id }}" data-stok="{{ $barang->stok_baik }}"
                                            data-minimum="{{ $barang->stok_minimum ?? 0 }}" data-satuan="{{ $barang->satuan }}"
                                            data-nama="{{ $barang->nama_barang }}" data-kode="{{ $barang->kode_barang }}"
                                            @selected((string) old('barang_id') === (string) $barang->id)>{{ $barang->kode_barang }} · {{ $barang->nama_barang }}
                                            (stok {{ number_format($barang->stok_baik, 0, ',', '.') }} {{ $barang->satuan }})</option>
                                        @endforeach
                                    </select>
                                    @error('barang_id')
                                    <div class="tx-field-error" role="alert">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-5" data-tx-field>
                                    <label class="form-label" for="jumlah">Jumlah keluar <span
                                            class="fe-required">*</span></label>
                                    <input id="jumlah" name="jumlah" type="number" min="1" step="1" inputmode="numeric"
                                        class="form-control @error('jumlah') is-invalid @enderror" placeholder="0"
                                        value="{{ old('jumlah') }}" data-tx-jumlah data-tx-cek-stok
                                        data-label="Jumlah keluar" required>
                                    @error('jumlah')
                                    <div class="tx-field-error" role="alert">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-7" data-tx-field>
                                    <label class="form-label" for="tanggal">Tanggal keluar <span
                                            class="fe-required">*</span></label>
                                    <input id="tanggal" name="tanggal" type="datetime-local"
                                        class="form-control @error('tanggal') is-invalid @enderror"
                                        value="{{ old('tanggal', $waktuSekarang) }}" max="{{ $waktuSekarang }}"
                                        data-label="Tanggal keluar" data-tx-tanggal required>
                                    @error('tanggal')
                                    <div class="tx-field-error" role="alert">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12" data-tx-field>
                                    <label class="form-label" for="tujuan">Tujuan / penerima <span
                                            class="fe-required">*</span></label>
                                    <input id="tujuan" name="tujuan" type="text" maxlength="255"
                                        class="form-control @error('tujuan') is-invalid @enderror"
                                        placeholder="Contoh: Ruang administrasi, Lab komputer 2"
                                        value="{{ old('tujuan') }}" data-label="Tujuan" required>
                                    @error('tujuan')
                                    <div class="tx-field-error" role="alert">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            @include('petugas.partials.panel-stok', ['mode' => 'keluar'])
                        </div>
                    </div>
                </div>
                <div class="tx-form-actions">
                    <p>Stok baik berkurang setelah pengeluaran disetujui. Nomor transaksi <kbd>BK-</kbd> dibuat
                        otomatis.</p>
                    <div class="tx-form-buttons">
                        <button type="reset" class="btn fe-btn-secondary" data-tx-reset>Kosongkan</button>
                        <button type="submit" class="btn fe-btn-primary" data-tx-submit @disabled($barangs->isEmpty())>Tinjau
                            &amp; setujui</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Log Barang Keluar</h2>
                @if($barangKeluars->total() > 0)
                <span>Menampilkan {{ $barangKeluars->firstItem() }}–{{ $barangKeluars->lastItem() }} dari
                    {{ number_format($barangKeluars->total(), 0, ',', '.') }} transaksi</span>
                @else
                <span>0 transaksi</span>
                @endif
            </div>
            @if($barangKeluars->isEmpty())
            <p class="fe-empty">Belum ada transaksi barang keluar.</p>
            @else
            <div class="table-responsive">
                <table class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>No. transaksi</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Tujuan</th>
                            <th>Disetujui oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangKeluars as $transaksi)
                        <tr>
                            <td class="text-nowrap">{{ $transaksi->tanggal?->format('d/m/Y') }}
                                <div><small class="text-muted">{{ $transaksi->tanggal?->format('H:i') }}</small></div>
                            </td>
                            <td><span class="tx-nomor">{{ $transaksi->nomor_transaksi }}</span></td>
                            <td>
                                <div class="fe-cell-title">{{ $transaksi->barang?->nama_barang ?? '—' }}</div>
                                <small class="text-muted">{{ $transaksi->barang?->kode_barang }}</small>
                            </td>
                            <td class="text-nowrap"><span class="fe-badge fe-badge-red">−{{ number_format($transaksi->jumlah, 0, ',', '.') }}
                                    {{ $transaksi->barang?->satuan }}</span></td>
                            <td class="fe-keterangan">{{ $transaksi->tujuan }}</td>
                            <td>{{ $transaksi->user?->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($barangKeluars->hasPages())
            <div class="fe-pagination">{{ $barangKeluars->links('pagination::bootstrap-5') }}</div>
            @endif
            @endif
        </section>

        <div class="modal fade fe-modal tx-modal" id="txApprovalModal" tabindex="-1" aria-labelledby="txApprovalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="fe-eyebrow">PERSETUJUAN PENGELUARAN</div>
                            <h2 class="modal-title fs-5" id="txApprovalTitle">Setujui pengeluaran stok?</h2>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="fe-detail-list">
                            <dt>Barang</dt>
                            <dd data-tx-approval="barang">—</dd>
                            <dt>Jumlah keluar</dt>
                            <dd data-tx-approval="jumlah">—</dd>
                            <dt>Stok baik sekarang</dt>
                            <dd data-tx-approval="stok">—</dd>
                            <dt>Stok setelah keluar</dt>
                            <dd data-tx-approval="sisa">—</dd>
                            <dt>Tujuan / penerima</dt>
                            <dd data-tx-approval="tujuan">—</dd>
                            <dt>Tanggal keluar</dt>
                            <dd data-tx-approval="tanggal">—</dd>
                        </dl>
                        <div class="tx-alert tx-alert-warning d-none mt-3 mb-0" data-tx-approval-warning role="status">
                        </div>
                        <div class="tx-check mt-3" data-tx-field>
                            <input class="form-check-input" type="checkbox" id="txApprovalCheck"
                                data-tx-approval-check>
                            <label class="form-check-label" for="txApprovalCheck">Saya sudah memeriksa fisik barang dan
                                jumlahnya sesuai dengan data di atas.</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn fe-btn-secondary" data-bs-dismiss="modal">Periksa lagi</button>
                        <button type="button" class="btn fe-btn-primary" data-tx-approval-confirm>Setujui &amp; keluarkan
                            stok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('petugas.partials.transaksi-scripts')
</x-layout>
