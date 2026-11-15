<form method="POST" action="{{ $url }}" class="d-inline-block fe-delete-form" data-label="{{ $label }}">@csrf
    @method('DELETE')<button type="submit" class="btn fe-btn-danger" aria-label="Hapus {{ $label }}">Hapus</button>
</form>