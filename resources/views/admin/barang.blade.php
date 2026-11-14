<x-layout><x-slot:title>Data Induk Barang · Gudang 2B</x-slot:title>
    @include('admin.partials.assets')
    <div class="fe-page">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">MASTER DATA / BARANG</div>
                <h1>Data Induk Barang</h1>
                <p>Kelola katalog, spesifikasi, dan batas stok minimum.</p>
            </div><button class="btn fe-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Tambah
                barang</button>
        </div>
        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Katalog barang</h2><span>{{ $barangs->count() }} barang tercatat</span>
            </div>
            <div class="table-responsive">
                <table id="barangTable" class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Kategori</th>
                            <th>Stok baik</th>
                            <th>Stok minimum</th>
                            <th>Status</th>
                            <th class="fe-actions-col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>@foreach($barangs as $item)
                        <tr>
                            <td>
                                <div class="fe-product">
                                    <div class="fe-thumb">@if($item->gambar)<img
                                        src="{{ asset('storage/' . $item->gambar) }}"
                                    alt="Foto {{ $item->nama_barang }}" loading="lazy">@else<span>▦</span>@endif
                                    </div>
                                    <div>
                                        <div class="fe-cell-title">{{ $item->nama_barang }}</div>
                                        <small>{{ $item->kode_barang }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->kategori?->nama_kategori ?? '—' }}</td>
                            <td data-order="{{ $item->stok_baik }}">{{ number_format($item->stok_baik, 0, ',', '.') }}
                                {{ $item->satuan }}</td>
                            <td data-order="{{ $item->stok_minimum }}">{{ $item->stok_minimum }}</td>
                            <td><span
                                    class="fe-badge {{ $item->stok_baik <= $item->stok_minimum ? 'fe-badge-red' : 'fe-badge-green' }}">{{ $item->stok_baik <= $item->stok_minimum ? 'Perlu perhatian' : 'Tersedia' }}</span>
                            </td>
                            <td>
                                <div class="fe-actions"><button type="button" class="btn fe-btn-light fe-detail"
                                        data-bs-toggle="modal" data-bs-target="#detailModal"
                                        data-kode-barang="{{ $item->kode_barang }}"
                                        data-nama-barang="{{ $item->nama_barang }}"
                                        data-kategori="{{ $item->kategori?->nama_kategori }}"
                                        data-satuan="{{ $item->satuan }}" data-stok-baik="{{ $item->stok_baik }}"
                                        data-stok-rusak-ringan="{{ $item->stok_rusak_ringan }}"
                                        data-stok-rusak="{{ $item->stok_rusak }}"
                                        data-stok-dipinjam="{{ $item->stok_dipinjam }}"
                                        data-stok-minimum="{{ $item->stok_minimum }}"
                                        data-keterangan="{{ $item->keterangan }}"
                                        data-gambar="{{ $item->gambar ? asset('storage/' . $item->gambar) : '' }}">Detail</button><button
                                        type="button" class="btn fe-btn-light fe-edit" data-bs-toggle="modal"
                                        data-bs-target="#editModal" data-id="{{ $item->id }}"
                                        data-kode-barang="{{ $item->kode_barang }}"
                                        data-nama-barang="{{ $item->nama_barang }}"
                                        data-kategori-id="{{ $item->kategori_id }}" data-satuan="{{ $item->satuan }}"
                                        data-stok-minimum="{{ $item->stok_minimum }}"
                                        data-keterangan="{{ $item->keterangan }}">Edit</button>@include('admin.partials.delete', ['url' => url('admin/barang/' . $item->id), 'label' => $item->nama_barang])
                                </div>
                            </td>
                    </tr>@endforeach
                    </tbody>
                </table>
            </div>
            <p class="fe-note">Status dihitung berdasarkan stok baik dibandingkan stok minimum. Stok transaksi dikelola
                melalui modul terkait.</p>
        </section>@php($prefix = 'add')
        <div class="modal fade fe-modal" id="addModal" tabindex="-1" aria-labelledby="addModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="fe-eyebrow">FORMULIR DATA</div>
                            <h2 class="modal-title fs-5" id="addModalTitle">Tambah barang</h2>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>
                    <form method="POST" action="{{ url('admin/barang') }}" enctype="multipart/form-data">@csrf <div
                            class="modal-body">
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_kode_barang">Kode barang
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_kode_barang"
                                    name="kode_barang" type="text" class="form-control" required maxlength="50"
                                    value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_nama_barang">Nama barang
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_nama_barang"
                                    name="nama_barang" type="text" class="form-control" required value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_kategori_id">Kategori <span
                                        class="fe-required">*</span></label><select name="kategori_id"
                                    id="{{ $prefix }}_kategori_id" class="form-select" required>
                                    <option value="">Pilih kategori</option>@foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                    @endforeach
                                </select></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_satuan">Satuan <span
                                        class="fe-required">*</span></label><input id="{{ $prefix }}_satuan"
                                    name="satuan" type="text" class="form-control" required maxlength="50"
                                    placeholder="Contoh: unit, pcs" value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_stok_minimum">Stok minimum
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_stok_minimum"
                                    name="stok_minimum" type="number" class="form-control" required min="0" value="">
                            </div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_gambar">Foto barang
                                    (opsional)</label><input id="{{ $prefix }}_gambar" name="gambar" type="file"
                                    class="form-control" accept="image/jpeg,image/png,image/webp" value="">
                                <div class="form-text">JPEG, PNG, atau WebP. Maksimal 2 MB.</div>
                            </div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_keterangan">Keterangan /
                                    spesifikasi</label><textarea id="{{ $prefix }}_keterangan" name="keterangan"
                                    class="form-control" rows="3"></textarea></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn fe-btn-secondary"
                                data-bs-dismiss="modal">Batal</button><button type="submit"
                                class="btn fe-btn-primary">Simpan data</button></div>
                    </form>
                </div>
            </div>
        </div>@php($prefix = 'edit')
        <div class="modal fade fe-modal" id="editModal" tabindex="-1" aria-labelledby="editModalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="fe-eyebrow">FORMULIR DATA</div>
                            <h2 class="modal-title fs-5" id="editModalTitle">Edit barang</h2>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>
                    <form method="POST" data-action-template="{{ url('admin/barang') }}/__ID__" action="#"
                        enctype="multipart/form-data">@csrf @method('PUT')<input type="hidden" name="_edit_id" value="">
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_kode_barang">Kode barang
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_kode_barang"
                                    name="kode_barang" type="text" class="form-control" required maxlength="50"
                                    value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_nama_barang">Nama barang
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_nama_barang"
                                    name="nama_barang" type="text" class="form-control" required value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_kategori_id">Kategori <span
                                        class="fe-required">*</span></label><select name="kategori_id"
                                    id="{{ $prefix }}_kategori_id" class="form-select" required>
                                    <option value="">Pilih kategori</option>@foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                                    @endforeach
                                </select></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_satuan">Satuan <span
                                        class="fe-required">*</span></label><input id="{{ $prefix }}_satuan"
                                    name="satuan" type="text" class="form-control" required maxlength="50"
                                    placeholder="Contoh: unit, pcs" value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_stok_minimum">Stok minimum
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_stok_minimum"
                                    name="stok_minimum" type="number" class="form-control" required min="0" value="">
                            </div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_gambar">Foto barang
                                    (opsional)</label><input id="{{ $prefix }}_gambar" name="gambar" type="file"
                                    class="form-control" accept="image/jpeg,image/png,image/webp" value="">
                                <div class="form-text">JPEG, PNG, atau WebP. Maksimal 2 MB.</div>
                            </div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_keterangan">Keterangan /
                                    spesifikasi</label><textarea id="{{ $prefix }}_keterangan" name="keterangan"
                                    class="form-control" rows="3"></textarea></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn fe-btn-secondary"
                                data-bs-dismiss="modal">Batal</button><button type="submit"
                                class="btn fe-btn-primary">Simpan data</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade fe-modal" id="detailModal" tabindex="-1" aria-labelledby="barangDetailTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="fe-eyebrow">KATALOG INVENTARIS</div>
                            <h2 class="modal-title fs-5" id="barangDetailTitle">Detail barang</h2>
                        </div><button class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body"><img class="fe-detail-image d-none" alt="Foto barang" id="detailImage">
                        <dl class="fe-detail-list">
                            <dt>Kode barang</dt>
                            <dd data-detail="kode-barang"></dd>
                            <dt>Nama barang</dt>
                            <dd data-detail="nama-barang"></dd>
                            <dt>Kategori</dt>
                            <dd data-detail="kategori"></dd>
                            <dt>Satuan</dt>
                            <dd data-detail="satuan"></dd>
                            <dt>Stok baik</dt>
                            <dd data-detail="stok-baik"></dd>
                            <dt>Rusak ringan</dt>
                            <dd data-detail="stok-rusak-ringan"></dd>
                            <dt>Rusak</dt>
                            <dd data-detail="stok-rusak"></dd>
                            <dt>Dipinjam</dt>
                            <dd data-detail="stok-dipinjam"></dd>
                            <dt>Stok minimum</dt>
                            <dd data-detail="stok-minimum"></dd>
                            <dt>Keterangan / spesifikasi</dt>
                            <dd data-detail="keterangan"></dd>
                        </dl>
                    </div>
                    <div class="modal-footer"><button class="btn fe-btn-secondary"
                            data-bs-dismiss="modal">Tutup</button></div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.validation')
    @include('admin.partials.scripts')
</x-layout>