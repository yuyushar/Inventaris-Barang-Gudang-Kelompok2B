<x-layout><x-slot:title>Barang Masuk · Gudang 2B</x-slot:title>
    {{-- FE 2 (Estu) · No. 19: Views & slicing form transaksi barang masuk (multi-item) --}}
    @include('petugas.partials.transaksi-assets')
    @php
        $maksimalBaris = 20;
        $barisLama = old('items');
        $barisForm = is_array($barisLama) && count($barisLama) > 0
            ? $barisLama
            : [0 => ['barang_id' => '', 'jumlah' => '']];
        $waktuSekarang = now()->format('Y-m-d\TH:i');
        $dataKosong = $suppliers->isEmpty() || $barangs->isEmpty();
    @endphp
    <div class="fe-page tx-page" data-tx-server-now="{{ now()->getTimestampMs() }}"
        data-tx-tz-offset="{{ now()->utcOffset() }}">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">TRANSAKSI / BARANG MASUK</div>
                <h1>Barang Masuk</h1>
                <p>Catat barang yang diterima dari supplier. Satu transaksi bisa berisi beberapa barang sekaligus.</p>
            </div>
            <a class="btn fe-btn-secondary" href="{{ route('petugas.riwayat-stok.index') }}">Lihat riwayat stok</a>
        </div>

        @if($errors->any())
        <div class="tx-alert tx-alert-danger" role="alert">
            <strong>Transaksi belum tersimpan.</strong>
            <ul>
                @foreach(array_unique($errors->all()) as $pesan)
                <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($dataKosong)
        <div class="tx-alert tx-alert-warning" role="status">
            <strong>Data induk belum lengkap.</strong>
            {{ $suppliers->isEmpty() ? 'Belum ada supplier.' : '' }}
            {{ $barangs->isEmpty() ? 'Belum ada barang.' : '' }}
            Minta admin menambahkannya lebih dulu sebelum mencatat barang masuk.
        </div>
        @endif

        <section class="fe-card mb-3">
            <div class="fe-card-header">
                <h2>Form transaksi barang masuk</h2>
                <span>Maksimal {{ $maksimalBaris }} barang per transaksi</span>
            </div>
            <form method="POST" action="{{ route('petugas.barang-masuk.store') }}" class="tx-form"
                data-tx-form="masuk" data-tx-max-rows="{{ $maksimalBaris }}">
                @csrf
                <div class="fe-card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6" data-tx-field>
                            <label class="form-label" for="supplier_id">Supplier <span class="fe-required">*</span></label>
                            <select id="supplier_id" name="supplier_id"
                                class="form-select @error('supplier_id') is-invalid @enderror" data-label="Supplier"
                                required>
                                <option value="">Pilih supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->nama_supplier }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                            <div class="tx-field-error" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6" data-tx-field>
                            <label class="form-label" for="tanggal">Tanggal diterima <span
                                    class="fe-required">*</span></label>
                            <input id="tanggal" name="tanggal" type="datetime-local"
                                class="form-control @error('tanggal') is-invalid @enderror"
                                value="{{ old('tanggal', $waktuSekarang) }}" max="{{ $waktuSekarang }}"
                                data-label="Tanggal diterima" data-tx-tanggal required>
                            @error('tanggal')
                            <div class="tx-field-error" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="tx-items-head">
                        <h3>Daftar barang</h3>
                        <span data-tx-row-counter>{{ count($barisForm) }} dari {{ $maksimalBaris }} baris</span>
                    </div>
                    @error('items')
                    <div class="tx-field-error mb-2" role="alert">{{ $message }}</div>
                    @enderror
                    <div class="table-responsive tx-items-wrap">
                        <table class="table fe-table tx-items-table align-middle w-100">
                            <thead>
                                <tr>
                                    <th class="tx-item-no">No</th>
                                    <th>Barang</th>
                                    <th>Stok saat ini</th>
                                    <th>Jumlah masuk</th>
                                    <th>Stok setelah</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody data-tx-items>
                                @foreach($barisForm as $indeks => $baris)
                                @include('petugas.partials.barang-masuk-row', [
                                    'indeks' => $indeks,
                                    'nomor' => $loop->iteration,
                                    'baris' => is_array($baris) ? $baris : [],
                                ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <template data-tx-item-template>
                        @include('petugas.partials.barang-masuk-row', [
                            'indeks' => '__INDEX__',
                            'nomor' => '__NOMOR__',
                            'baris' => [],
                        ])
                    </template>

                    <div class="tx-items-footer">
                        <button type="button" class="btn fe-btn-secondary" data-tx-add-row @disabled($dataKosong)>+ Tambah
                            baris barang</button>
                        <div class="tx-total" aria-live="polite">
                            <span>Total</span>
                            <strong data-tx-total-jenis>0 jenis barang</strong>
                            <strong data-tx-total-unit>0 unit</strong>
                        </div>
                    </div>
                </div>
                <div class="tx-form-actions">
                    <p>Stok baik setiap barang akan bertambah dan nomor transaksi <kbd>BM-</kbd> dibuat otomatis per
                        baris.</p>
                    <div class="tx-form-buttons">
                        <button type="reset" class="btn fe-btn-secondary" data-tx-reset>Kosongkan</button>
                        <button type="submit" class="btn fe-btn-primary" data-tx-submit @disabled($dataKosong)>Simpan
                            transaksi</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Log Barang Masuk</h2>
                @if($barangMasuks->total() > 0)
                <span>Menampilkan {{ $barangMasuks->firstItem() }}–{{ $barangMasuks->lastItem() }} dari
                    {{ number_format($barangMasuks->total(), 0, ',', '.') }} transaksi</span>
                @else
                <span>0 transaksi</span>
                @endif
            </div>
            @if($barangMasuks->isEmpty())
            <p class="fe-empty">Belum ada transaksi barang masuk.</p>
            @else
            <div class="table-responsive">
                <table class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>No. transaksi</th>
                            <th>Barang</th>
                            <th>Supplier</th>
                            <th>Jumlah</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($barangMasuks as $transaksi)
                        <tr>
                            <td class="text-nowrap">{{ $transaksi->tanggal?->format('d/m/Y') }}
                                <div><small class="text-muted">{{ $transaksi->tanggal?->format('H:i') }}</small></div>
                            </td>
                            <td><span class="tx-nomor">{{ $transaksi->nomor_transaksi }}</span></td>
                            <td>
                                <div class="fe-cell-title">{{ $transaksi->barang?->nama_barang ?? '—' }}</div>
                                <small class="text-muted">{{ $transaksi->barang?->kode_barang }}</small>
                            </td>
                            <td>{{ $transaksi->supplier?->nama_supplier ?? '—' }}</td>
                            <td class="text-nowrap"><span class="fe-badge fe-badge-green">+{{ number_format($transaksi->jumlah, 0, ',', '.') }}
                                    {{ $transaksi->barang?->satuan }}</span></td>
                            <td>{{ $transaksi->user?->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($barangMasuks->hasPages())
            <div class="fe-pagination">{{ $barangMasuks->links('pagination::bootstrap-5') }}</div>
            @endif
            @endif
        </section>
    </div>
    @include('petugas.partials.transaksi-scripts')
</x-layout>
