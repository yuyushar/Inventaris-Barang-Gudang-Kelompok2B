<x-layout><x-slot:title>Kelola Kategori · Gudang 2B</x-slot:title>
    @include('admin.partials.assets')
    <div class="fe-page">
        <div class="fe-heading">
            <div>
                <div class="fe-eyebrow">MASTER DATA / KATEGORI</div>
                <h1>Kelola Kategori</h1>
                <p>Susun pengelompokan barang agar mudah ditelusuri.</p>
            </div><button class="btn fe-btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">+ Tambah
                kategori</button>
        </div>
        <section class="fe-card">
            <div class="fe-card-header">
                <h2>Daftar kategori</h2><span>{{ $kategoris->count() }} kategori tercatat</span>
            </div>
            <div class="table-responsive">
                <table id="kategoriTable" class="table fe-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Nama kategori</th>
                            <th>Deskripsi</th>
                            <th>Dibuat</th>
                            <th class="fe-actions-col">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>@foreach($kategoris as $item)
                        <tr>
                            <td class="fe-cell-title">{{ $item->nama_kategori }}</td>
                            <td>{{ $item->deskripsi ?: '—' }}</td>
                            <td data-order="{{ $item->created_at?->timestamp }}">
                                {{ $item->created_at?->format('d M Y') }}</td>
                            <td>
                                <div class="fe-actions"><button type="button" class="btn fe-btn-light fe-edit"
                                        data-bs-toggle="modal" data-bs-target="#editModal" data-id="{{ $item->id }}"
                                        data-nama-kategori="{{ $item->nama_kategori }}"
                                        data-deskripsi="{{ $item->deskripsi }}">Edit</button>@include('admin.partials.delete', ['url' => url('admin/kategori/' . $item->id), 'label' => $item->nama_kategori])
                                </div>
                            </td>
                    </tr>@endforeach
                    </tbody>
                </table>
            </div>
            <p class="fe-note">Kategori yang masih digunakan oleh barang tidak dapat dihapus.</p>
        </section>@php($prefix = 'add')
        <div class="modal fade fe-modal" id="addModal" tabindex="-1" aria-labelledby="addModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <div class="fe-eyebrow">FORMULIR DATA</div>
                            <h2 class="modal-title fs-5" id="addModalTitle">Tambah kategori</h2>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>
                    <form method="POST" action="{{ url('admin/kategori') }}">@csrf <div class="modal-body">
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_nama_kategori">Nama kategori
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_nama_kategori"
                                    name="nama_kategori" type="text" class="form-control" required value=""></div>
                            <div class="mb-3"><label class="form-label"
                                    for="{{ $prefix }}_deskripsi">Deskripsi</label><textarea
                                    id="{{ $prefix }}_deskripsi" name="deskripsi" class="form-control"
                                    rows="3"></textarea></div>
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
                            <h2 class="modal-title fs-5" id="editModalTitle">Edit kategori</h2>
                        </div><button class="btn-close" type="button" data-bs-dismiss="modal"
                            aria-label="Tutup"></button>
                    </div>
                    <form method="POST" data-action-template="{{ url('admin/kategori') }}/__ID__" action="#">@csrf
                        @method('PUT')<input type="hidden" name="_edit_id" value="">
                        <div class="modal-body">
                            <div class="mb-3"><label class="form-label" for="{{ $prefix }}_nama_kategori">Nama kategori
                                    <span class="fe-required">*</span></label><input id="{{ $prefix }}_nama_kategori"
                                    name="nama_kategori" type="text" class="form-control" required value=""></div>
                            <div class="mb-3"><label class="form-label"
                                    for="{{ $prefix }}_deskripsi">Deskripsi</label><textarea
                                    id="{{ $prefix }}_deskripsi" name="deskripsi" class="form-control"
                                    rows="3"></textarea></div>
                        </div>
                        <div class="modal-footer"><button type="button" class="btn fe-btn-secondary"
                                data-bs-dismiss="modal">Batal</button><button type="submit"
                                class="btn fe-btn-primary">Simpan data</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('admin.partials.validation')
    @include('admin.partials.scripts')
</x-layout>