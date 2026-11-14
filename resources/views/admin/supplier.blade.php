<x-layout><x-slot:title>Kelola Supplier · Gudang 2B</x-slot:title>
    @include('admin.partials.assets')
    <div class="fe-page">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">MASTER DATA / SUPPLIER</div>
                <h1>Kelola Supplier</h1>
                <p>Simpan informasi kontak dan sumber pasokan barang.</p>
            </div><button class="btn fe-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Tambah
                supplier</button>
        </div>
        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Daftar supplier</h2><span>{{ $suppliers->count() }} supplier tercatat</span>
            </div>
            <div class="table-responsive">
                <table id="supplierTable" class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Nama supplier</th>
                            <th>Telepon</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th class="fe-actions-col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>@foreach($suppliers as $item)
                        <tr>
                            <td class="fe-cell-title">{{ $item->nama_supplier }}</td>
                            <td>{{ $item->telepon ?: '—' }}</td>
                            <td>{{ $item->email ?: '—' }}</td>
                            <td class="fe-truncate">{{ $item->alamat ?: '—' }}</td>
                            <td>
                                <div class="fe-actions"><button type="button" class="btn fe-btn-light fe-detail"
                                        data-bs-toggle="modal" data-bs-target="#detailModal"
                                        data-nama-supplier="{{ $item->nama_supplier }}"
                                        data-telepon="{{ $item->telepon }}" data-email="{{ $item->email }}"
                                        data-alamat="{{ $item->alamat }}">Detail</button><button type="button"
                                        class="btn fe-btn-light fe-edit" data-bs-toggle="modal"
                                        data-bs-target="#editModal" data-id="{{ $item->id }}"
                                        data-nama-supplier="{{ $item->nama_supplier }}"
                                        data-telepon="{{ $item->telepon }}" data-email="{{ $item->email }}"
                                        data-alamat="{{ $item->alamat }}">Edit</button>@include('admin.partials.delete', ['url' => url('admin/supplier/' . $item->id), 'label' => $item->nama_supplier])
                                </div>
                            </td>
                    </tr>@endforeach
                    </tbody>
                </table>
            </div>
        </section>@php($prefix = 'add')
        <div class="modal fade fe-modal" id="addModal" tabindex="-1" aria-labelledby="addModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="fe-eyebrow">FORMULIR DATA</div>
                            <h2 class="modal-title fs-5" id="addModalTitle">Tambah supplier</h2>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>
                    <form method="POST" action="{{ url('admin/supplier') }}">@csrf <div class="modal-body">
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_nama_supplier">Nama supplier
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_nama_supplier"
                                    name="nama_supplier" type="text" class="form-control" required value=""></div>
                            <div class="mb-3"><label class="form-label"
                                    for="{{ $prefix }}_telepon">Telepon</label><input id="{{ $prefix }}_telepon"
                                    name="telepon" type="tel" class="form-control" maxlength="20" value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_email">Email</label><input
                                    id="{{ $prefix }}_email" name="email" type="email" class="form-control" value="">
                            </div>
                            <div class="mb-3"><label class="form-label"
                                    for="{{ $prefix }}_alamat">Alamat</label><textarea id="{{ $prefix }}_alamat"
                                    name="alamat" class="form-control" rows="3"></textarea></div>
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
                            <h2 class="modal-title fs-5" id="editModalTitle">Edit supplier</h2>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>
                    <form method="POST" data-action-template="{{ url('admin/supplier') }}/__ID__" action="#">@csrf
                        @method('PUT')<input type="hidden" name="_edit_id" value="">
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_nama_supplier">Nama supplier
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_nama_supplier"
                                    name="nama_supplier" type="text" class="form-control" required value=""></div>
                            <div class="mb-3"><label class="form-label"
                                    for="{{ $prefix }}_telepon">Telepon</label><input id="{{ $prefix }}_telepon"
                                    name="telepon" type="tel" class="form-control" maxlength="20" value=""></div>
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_email">Email</label><input
                                    id="{{ $prefix }}_email" name="email" type="email" class="form-control" value="">
                            </div>
                            <div class="mb-3"><label class="form-label"
                                    for="{{ $prefix }}_alamat">Alamat</label><textarea id="{{ $prefix }}_alamat"
                                    name="alamat" class="form-control" rows="3"></textarea></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn fe-btn-secondary"
                                data-bs-dismiss="modal">Batal</button><button type="submit"
                                class="btn fe-btn-primary">Simpan data</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade fe-modal" id="detailModal" tabindex="-1" aria-labelledby="supplierDetailTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="supplierDetailTitle" class="modal-title fs-5">Detail supplier</h2><button
                            class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <dl class="fe-detail-list">
                            <dt>Nama supplier</dt>
                            <dd data-detail="nama-supplier"></dd>
                            <dt>Telepon</dt>
                            <dd data-detail="telepon"></dd>
                            <dt>Email</dt>
                            <dd data-detail="email"></dd>
                            <dt>Alamat</dt>
                            <dd data-detail="alamat"></dd>
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