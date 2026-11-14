<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
@if(session('success'))
<div class="fe-flash" data-type="success" data-message="{{ session('success') }}"></div>@endif
@if(session('error'))
<div class="fe-flash" data-type="error" data-message="{{ session('error') }}"></div>@endif
@if($errors->any())
<div class="fe-flash" data-type="error" data-message="{{ $errors->first() }}"></div>@endif
<script src="{{ asset('js/fatih-inventaris.js') }}"></script>