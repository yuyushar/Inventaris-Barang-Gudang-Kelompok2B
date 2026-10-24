<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Inventaris Gudang' }}</title>
    <!-- Load Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
    </style>
</head>
<!-- Tambahkan d-flex di body agar sidebar dan konten berjejer ke samping -->
<body class="d-flex">

    <!-- Panggil Komponen Sidebar -->
    <x-navbar>
        {{ $menu ?? '' }}
    </x-navbar>

    <!-- Konten Utama Halaman -->
    <div class="flex-grow-1 p-4 w-100">
        {{ $slot }}
    </div>

    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>