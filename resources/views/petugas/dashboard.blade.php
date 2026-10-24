<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Petugas</title>
</head>

<body style="background-color: #d1ecf1; padding: 20px;">
    <h1>Selamat Datang, Petugas Gudang! 📦</h1>
    <p>Anda login sebagai: <b>{{ Auth::user()->name }}</b></p>
    <p>Di halaman ini nanti Anda akan menginput Barang Masuk, Barang Keluar, dan Peminjaman.</p>

    <form action="/logout" method="POST">
        @csrf
        <button type="submit" style="padding: 10px; background: red; color: white;">Logout</button>
    </form>
</body>

</html>
