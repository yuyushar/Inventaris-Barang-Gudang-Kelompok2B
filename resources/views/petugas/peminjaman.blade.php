<x-layout><x-slot:title>Transaksi Peminjaman · Gudang 2B</x-slot:title>
    {{-- FE 2 (Estu) · No. 21 & 22: Views form peminjaman & pengembalian + upload & preview foto bukti --}}
    @include('petugas.partials.transaksi-assets', ['filepond' => true])
    @php
        $waktuSekarang = now()->format('Y-m-d\TH:i');
        $formAktif = old('_form');
        $gagalPinjam = $errors->any() && $formAktif !== 'pengembalian';
        $gagalKembali = $errors->any() && $formAktif === 'pengembalian';
        $errorPinjam = fn (string $field) => $gagalPinjam ? $errors->first($field) : null;
        $namaKondisi = [
            'baik' => 'Baik',
            'rusak_ringan' => 'Rusak ringan',
            'rusak' => 'Rusak',
        ];
        $pilihanKondisi = [
            'baik' => ['Baik', 'Kembali ke stok baik dan bisa dipinjam lagi.', 'green'],
            'rusak_ringan' => ['Rusak ringan', 'Masih bisa diperbaiki. Masuk ke stok rusak ringan.', 'amber'],
            'rusak' => ['Rusak', 'Tidak layak pakai. Masuk ke stok rusak.', 'red'],
        ];
    @endphp
    <div class="fe-page tx-page" data-tx-server-now="{{ now()->getTimestampMs() }}"
        data-tx-tz-offset="{{ now()->utcOffset() }}">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">TRANSAKSI / PEMINJAMAN</div>
                <h1>Peminjaman &amp; Pengembalian</h1>
                <p>Catat siapa yang meminjam, kapan, dan lampirkan foto bukti saat barang dipinjam maupun
                    dikembalikan.</p>
            </div>
            <a class="btn fe-btn-secondary" href="{{ route('petugas.riwayat-stok.index') }}">Lihat riwayat stok</a>
        </div>

        @if($errors->any())
        <div class="tx-alert tx-alert-danger" role="alert">
            <strong>{{ $gagalKembali ? 'Pengembalian belum tersimpan.' : 'Peminjaman belum tersimpan.' }}</strong>
            <ul>
                @foreach(array_unique($errors->all()) as $pesan)
                <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($barangs->isEmpty())
        <div class="tx-alert tx-alert-warning" role="status">
            <strong>Tidak ada barang yang bisa dipinjam.</strong> Stok baik semua barang sedang kosong.
        </div>
        @endif

        <section class="fe-card mb-3">
            <div class="fe-card-header">
                <h2>Form peminjaman barang</h2>
                <span>Barang yang dipinjam harus dalam kondisi baik</span>
            </div>
            <form method="POST" action="{{ route('petugas.peminjaman.store') }}" class="tx-form"
                enctype="multipart/form-data" data-tx-form="peminjaman">
                @csrf
                <input type="hidden" name="_form" value="peminjaman">
                <div class="fe-card-body">
                    <div class="row g-3">
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-12" data-tx-field>
                                    <label class="form-label" for="barang_id">Barang <span
                                            class="fe-required">*</span></label>
                                    <select id="barang_id" name="barang_id"
                                        class="form-select {{ $errorPinjam('barang_id') ? 'is-invalid' : '' }}"
                                        data-tx-barang data-label="Barang" required>
                                        <option value="">Pilih barang</option>
                                        @foreach($barangs as $barang)
                                        <option value="{{ $barang->id }}" data-stok="{{ $barang->stok_baik }}"
                                            data-satuan="{{ $barang->satuan }}" data-nama="{{ $barang->nama_barang }}"
                                            data-kode="{{ $barang->kode_barang }}"
                                            @selected($gagalPinjam && (string) old('barang_id') === (string) $barang->id)>{{ $barang->kode_barang }} · {{ $barang->nama_barang }}
                                            (stok {{ number_format($barang->stok_baik, 0, ',', '.') }} {{ $barang->satuan }})</option>
                                        @endforeach
                                    </select>
                                    @if($errorPinjam('barang_id'))
                                    <div class="tx-field-error" role="alert">{{ $errorPinjam('barang_id') }}</div>
                                    @endif
                                </div>
                                <div class="col-sm-7" data-tx-field>
                                    <label class="form-label" for="peminjam_nama">Nama peminjam <span
                                            class="fe-required">*</span></label>
                                    <input id="peminjam_nama" name="peminjam_nama" type="text" maxlength="255"
                                        autocomplete="off"
                                        class="form-control {{ $errorPinjam('peminjam_nama') ? 'is-invalid' : '' }}"
                                        placeholder="Nama lengkap peminjam"
                                        value="{{ $gagalPinjam ? old('peminjam_nama') : '' }}" data-label="Nama peminjam"
                                        required>
                                    @if($errorPinjam('peminjam_nama'))
                                    <div class="tx-field-error" role="alert">{{ $errorPinjam('peminjam_nama') }}</div>
                                    @endif
                                </div>
                                <div class="col-sm-5" data-tx-field>
                                    <label class="form-label" for="jumlah">Jumlah <span
                                            class="fe-required">*</span></label>
                                    <input id="jumlah" name="jumlah" type="number" min="1" step="1" inputmode="numeric"
                                        class="form-control {{ $errorPinjam('jumlah') ? 'is-invalid' : '' }}"
                                        placeholder="0" value="{{ $gagalPinjam ? old('jumlah') : '' }}" data-tx-jumlah
                                        data-tx-cek-stok data-label="Jumlah" required>
                                    @if($errorPinjam('jumlah'))
                                    <div class="tx-field-error" role="alert">{{ $errorPinjam('jumlah') }}</div>
                                    @endif
                                </div>
                                <div class="col-12" data-tx-field>
                                    <label class="form-label" for="tanggal_pinjam">Tanggal &amp; jam pinjam <span
                                            class="fe-required">*</span></label>
                                    <input id="tanggal_pinjam" name="tanggal_pinjam" type="datetime-local"
                                        class="form-control {{ $errorPinjam('tanggal_pinjam') ? 'is-invalid' : '' }}"
                                        value="{{ $gagalPinjam ? old('tanggal_pinjam', $waktuSekarang) : $waktuSekarang }}"
                                        max="{{ $waktuSekarang }}" data-label="Tanggal pinjam" data-tx-tanggal required>
                                    <div class="form-text">Terisi otomatis dengan waktu sekarang. Ubah bila barang sudah
                                        dibawa lebih dulu.</div>
                                    @if($errorPinjam('tanggal_pinjam'))
                                    <div class="tx-field-error" role="alert">{{ $errorPinjam('tanggal_pinjam') }}</div>
                                    @endif
                                </div>
                                <div class="col-12">
                                    @include('petugas.partials.upload-bukti', [
                                        'id' => 'gambar_bukti_pinjam',
                                        'name' => 'gambar_bukti_pinjam',
                                        'label' => 'Foto bukti pinjam',
                                        'formGagal' => $gagalPinjam,
                                    ])
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            @include('petugas.partials.panel-stok', ['mode' => 'peminjaman'])
                        </div>
                    </div>
                </div>
                <div class="tx-form-actions">
                    <p>Stok baik berpindah ke stok dipinjam. Nomor peminjaman <kbd>PM-</kbd> dibuat otomatis.</p>
                    <div class="tx-form-buttons">
                        <button type="reset" class="btn fe-btn-secondary" data-tx-reset>Kosongkan</button>
                        <button type="submit" class="btn fe-btn-primary" data-tx-submit @disabled($barangs->isEmpty())>Catat
                            peminjaman</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Log Peminjaman dan Pengembalian</h2>
                @if($peminjamans->total() > 0)
                <span>Menampilkan {{ $peminjamans->firstItem() }}–{{ $peminjamans->lastItem() }} dari
                    {{ number_format($peminjamans->total(), 0, ',', '.') }} peminjaman</span>
                @else
                <span>0 peminjaman</span>
                @endif
            </div>
            @if($peminjamans->isEmpty())
            <p class="fe-empty">Belum ada transaksi peminjaman.</p>
            @else
            <div class="table-responsive">
                <table class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Peminjaman</th>
                            <th>Barang</th>
                            <th>Peminjam</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Foto bukti</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($peminjamans as $peminjaman)
                        @php
                            $masihDipinjam = $peminjaman->status === 'dipinjam';
                            $lamaHari = $peminjaman->tanggal_pinjam
                                ? (int) $peminjaman->tanggal_pinjam->copy()->startOfDay()->diffInDays(today())
                                : 0;
                            $urlBuktiPinjam = $peminjaman->gambar_bukti_pinjam ? asset('storage/'.$peminjaman->gambar_bukti_pinjam) : null;
                            $urlBuktiKembali = $peminjaman->gambar_bukti_kembali ? asset('storage/'.$peminjaman->gambar_bukti_kembali) : null;
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                <span class="tx-nomor">{{ $peminjaman->nomor_peminjaman }}</span>
                                <div><small class="text-muted">{{ $peminjaman->tanggal_pinjam?->format('d/m/Y H:i') }}</small></div>
                            </td>
                            <td>
                                <div class="fe-cell-title">{{ $peminjaman->barang?->nama_barang ?? '—' }}</div>
                                <small class="text-muted">{{ $peminjaman->barang?->kode_barang }}</small>
                            </td>
                            <td>
                                {{ $peminjaman->peminjam_nama }}
                                <div><small class="text-muted">dicatat {{ $peminjaman->user?->name ?? '—' }}</small></div>
                            </td>
                            <td class="text-nowrap">{{ number_format($peminjaman->jumlah, 0, ',', '.') }}
                                {{ $peminjaman->barang?->satuan }}</td>
                            <td>
                                @if($masihDipinjam)
                                <span class="fe-badge fe-badge-blue">Dipinjam</span>
                                <div><small class="text-muted">{{ $lamaHari === 0 ? 'Sejak hari ini' : $lamaHari.' hari' }}</small></div>
                                @else
                                <span class="fe-badge fe-badge-green">Dikembalikan</span>
                                <div><small class="text-muted">{{ $namaKondisi[$peminjaman->kondisi_saat_dikembalikan] ?? $peminjaman->kondisi_saat_dikembalikan }}
                                        · {{ $peminjaman->tanggal_kembali?->format('d/m/Y H:i') }}</small></div>
                                @endif
                            </td>
                            <td>
                                <div class="tx-bukti">
                                    @if($urlBuktiPinjam)
                                    <a href="{{ $urlBuktiPinjam }}" target="_blank" rel="noopener" class="tx-bukti-thumb"
                                        title="Foto bukti pinjam"><img src="{{ $urlBuktiPinjam }}"
                                            alt="Foto bukti pinjam {{ $peminjaman->nomor_peminjaman }}" loading="lazy"><span>Pinjam</span></a>
                                    @endif
                                    @if($urlBuktiKembali)
                                    <a href="{{ $urlBuktiKembali }}" target="_blank" rel="noopener" class="tx-bukti-thumb"
                                        title="Foto bukti kembali"><img src="{{ $urlBuktiKembali }}"
                                            alt="Foto bukti kembali {{ $peminjaman->nomor_peminjaman }}" loading="lazy"><span>Kembali</span></a>
                                    @endif
                                    @if(! $urlBuktiPinjam && ! $urlBuktiKembali)
                                    <span class="text-muted">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                @if($masihDipinjam)
                                <button type="button" class="btn fe-btn-light" data-bs-toggle="modal"
                                    data-bs-target="#txReturnModal" data-tx-return-btn
                                    data-id="{{ $peminjaman->id }}"
                                    data-action="{{ route('petugas.peminjaman.return', $peminjaman) }}"
                                    data-nomor="{{ $peminjaman->nomor_peminjaman }}"
                                    data-barang="{{ $peminjaman->barang?->nama_barang ?? '—' }}"
                                    data-peminjam="{{ $peminjaman->peminjam_nama }}"
                                    data-jumlah="{{ number_format($peminjaman->jumlah, 0, ',', '.') }} {{ $peminjaman->barang?->satuan }}"
                                    data-tanggal="{{ $peminjaman->tanggal_pinjam?->format('d/m/Y H:i') }}"
                                    data-bukti="{{ $urlBuktiPinjam }}">Proses pengembalian</button>
                                @else
                                <small class="text-muted">Diterima {{ $peminjaman->pengembali?->name ?? '—' }}</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($peminjamans->hasPages())
            <div class="fe-pagination">{{ $peminjamans->links('pagination::bootstrap-5') }}</div>
            @endif
            @endif
        </section>

        <div class="modal fade fe-modal tx-modal" id="txReturnModal" tabindex="-1" aria-labelledby="txReturnTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="fe-eyebrow">PENGEMBALIAN BARANG</div>
                            <h2 class="modal-title fs-5" id="txReturnTitle">Proses pengembalian</h2>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <form method="POST" action="#" enctype="multipart/form-data" data-tx-form="pengembalian"
                        data-tx-return-form>
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="pengembalian">
                        <input type="hidden" name="_return_id" value="{{ $gagalKembali ? old('_return_id') : '' }}"
                            data-tx-return-id>
                        <div class="modal-body">
                            <div class="tx-return-summary">
                                <img class="d-none" alt="Foto bukti saat dipinjam" data-tx-return-photo>
                                <dl class="fe-detail-list">
                                    <dt>No. peminjaman</dt>
                                    <dd data-tx-return="nomor">—</dd>
                                    <dt>Barang</dt>
                                    <dd data-tx-return="barang">—</dd>
                                    <dt>Peminjam</dt>
                                    <dd data-tx-return="peminjam">—</dd>
                                    <dt>Jumlah</dt>
                                    <dd data-tx-return="jumlah">—</dd>
                                    <dt>Dipinjam sejak</dt>
                                    <dd data-tx-return="tanggal">—</dd>
                                </dl>
                            </div>

                            <fieldset class="tx-kondisi mt-3" data-tx-field data-tx-kondisi data-label="Kondisi barang">
                                <legend class="form-label">Kondisi saat dikembalikan <span
                                        class="fe-required">*</span></legend>
                                @foreach($pilihanKondisi as $nilai => [$label, $keterangan, $warna])
                                <label class="tx-kondisi-option tx-kondisi-{{ $warna }}">
                                    <input type="radio" name="kondisi_saat_dikembalikan" value="{{ $nilai }}" required
                                        @checked($gagalKembali && old('kondisi_saat_dikembalikan') === $nilai)>
                                    <span><strong>{{ $label }}</strong><small>{{ $keterangan }}</small></span>
                                </label>
                                @endforeach
                                @if($gagalKembali && $errors->has('kondisi_saat_dikembalikan'))
                                <div class="tx-field-error" role="alert">{{ $errors->first('kondisi_saat_dikembalikan') }}</div>
                                @endif
                            </fieldset>

                            <div class="mt-3">
                                @include('petugas.partials.upload-bukti', [
                                    'id' => 'gambar_bukti_kembali',
                                    'name' => 'gambar_bukti_kembali',
                                    'label' => 'Foto bukti kembali',
                                    'formGagal' => $gagalKembali,
                                ])
                            </div>
                            @if($gagalKembali && ($errors->has('peminjaman') || $errors->has('jumlah')))
                            <div class="tx-alert tx-alert-danger mt-3 mb-0" role="alert">
                                {{ $errors->first('peminjaman') ?: $errors->first('jumlah') }}</div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn fe-btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn fe-btn-primary" data-tx-submit>Simpan pengembalian</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('petugas.partials.transaksi-scripts', ['filepond' => true])
</x-layout>
