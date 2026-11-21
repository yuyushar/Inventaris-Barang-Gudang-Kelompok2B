<div class="tx-upload" data-tx-field data-tx-upload-field data-label="{{ $label }}">
    <label class="form-label" for="{{ $id }}">{{ $label }} <span class="fe-required">*</span></label>
    <input id="{{ $id }}" name="{{ $name }}" type="file" class="form-control tx-upload-input"
        accept="image/jpeg,image/png,image/webp" data-tx-upload required
        aria-describedby="{{ $id }}_hint">
    <div class="tx-upload-preview d-none" data-tx-upload-preview>
        <img alt="Pratinjau {{ mb_strtolower($label) }}">
        <div class="tx-upload-preview-meta">
            <strong data-tx-upload-name></strong>
            <small data-tx-upload-size></small>
        </div>
        <button type="button" class="btn fe-btn-danger" data-tx-upload-clear>Hapus foto</button>
    </div>
    <div id="{{ $id }}_hint" class="form-text">JPG, PNG, atau WebP · maksimal 2 MB. Foto wajib dilampirkan sebagai
        bukti.</div>
    @if($formGagal ?? false)
    @error($name)
    <div class="tx-field-error" role="alert">{{ $message }}</div>
    @enderror
    <div class="tx-field-hint">Pilih ulang foto bukti. Browser tidak menyimpan file yang sudah dipilih saat formulir
        ditolak.</div>
    @endif
</div>
