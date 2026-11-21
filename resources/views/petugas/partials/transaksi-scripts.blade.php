<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" defer></script>
@if($filepond ?? false)
<script src="https://cdn.jsdelivr.net/npm/filepond@4.32.14/dist/filepond.min.js" defer></script>
<script
    src="https://cdn.jsdelivr.net/npm/filepond-plugin-file-validate-type@1.2.9/dist/filepond-plugin-file-validate-type.min.js"
    defer></script>
<script
    src="https://cdn.jsdelivr.net/npm/filepond-plugin-file-validate-size@2.2.8/dist/filepond-plugin-file-validate-size.min.js"
    defer></script>
<script
    src="https://cdn.jsdelivr.net/npm/filepond-plugin-image-preview@4.6.12/dist/filepond-plugin-image-preview.min.js"
    defer></script>
@endif
@if(session('success'))
<div class="tx-flash d-none" data-type="success" data-message="{{ session('success') }}"></div>
@endif
@if(session('error'))
<div class="tx-flash d-none" data-type="error" data-message="{{ session('error') }}"></div>
@endif
<script src="{{ asset('js/estu-transaksi.js') }}" defer></script>
