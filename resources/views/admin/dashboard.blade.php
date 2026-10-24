<!DOCTYPE html>
<html>

<head>
    <title>Dashboard Admin</title>
</head>

<body style="background-color: #f8d7da; padding: 20px;">
    <h1>Selamat Datang, Bos Admin! 👑</h1>
    <p>Anda login sebagai: <b>{{ Auth::user()->name }}</b></p>
    <p>Di halaman ini nanti Anda bisa mengatur Pengguna, Kategori, Supplier, dan Data Barang.</p>

    <form action="/logout" method="POST">
        @csrf
        <button type="submit" style="padding: 10px; background: red; color: white;">Logout</button>
    </form>
</body>

</html>
