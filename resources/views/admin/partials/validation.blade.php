@if($errors->any())
<div class="fe-validation-context d-none" data-modal="{{ old('_edit_id') ? 'editModal' : 'addModal' }}" data-edit-id="{{ old('_edit_id') }}">
@foreach(['name','email','role','nama_supplier','telepon','alamat','nama_kategori','deskripsi','kode_barang','nama_barang','kategori_id','satuan','stok_minimum','keterangan'] as $field)
<span data-field="{{ $field }}" data-value="{{ old($field) }}"></span>
@endforeach
</div>
@endif

@if($errors->any())
<div class="fe-server-errors d-none" aria-hidden="true">
@foreach($errors->messages() as $field => $messages)
@foreach($messages as $message)
<span data-error-field="{{ $field }}" data-error-message="{{ $message }}"></span>
@endforeach
@endforeach
</div>
@endif
