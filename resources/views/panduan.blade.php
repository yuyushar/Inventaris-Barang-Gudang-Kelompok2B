<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#192432">
    <title>Panduan Penggunaan · Gudang 2B</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php
        $urlKembali = auth()->check()
            ? (auth()->user()->role === 'admin' ? '/admin/dashboard' : '/petugas/dashboard')
            : '/login';
        $labelKembali = auth()->check() ? 'Kembali ke dashboard' : 'Masuk ke aplikasi';
    @endphp
    <style>
        :root {
            --ink: #0f172a;
            --muted: #475569;
            --line: #e2e8f0;
            --bg: #f4f6f9;
            --card: #ffffff;
            --navy: #192432;
            --blue: #2563eb;
            --blue-soft: #eff6ff;
            --green: #15803d;
            --green-soft: #f0fdf4;
            --amber: #b45309;
            --amber-soft: #fffbeb;
            --red: #b91c1c;
            --red-soft: #fef2f2;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 84px;
        }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 15px;
            line-height: 1.65;
            color: var(--ink);
            background: var(--bg);
        }

        a {
            color: var(--blue);
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px clamp(16px, 4vw, 40px);
            background: var(--navy);
            color: #fff;
        }

        .brand {
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .brand strong {
            font-weight: 800;
        }

        .brand small {
            margin-left: 8px;
            font-size: 12px;
            font-weight: 500;
            color: #94a3b8;
        }

        .btn-kembali {
            padding: 8px 15px;
            border-radius: 9px;
            background: #fff;
            color: var(--navy);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-kembali:hover {
            background: #e2e8f0;
        }

        .btn-kembali:focus-visible,
        .toc a:focus-visible,
        .mulai a:focus-visible {
            outline: 2px solid #93c5fd;
            outline-offset: 2px;
        }

        .hero {
            padding: clamp(28px, 6vw, 56px) clamp(16px, 4vw, 40px) 8px;
            max-width: 1180px;
            margin: 0 auto;
        }

        .hero .eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--blue);
        }

        .hero h1 {
            margin: 6px 0 8px;
            font-size: clamp(26px, 4.4vw, 38px);
            line-height: 1.15;
            letter-spacing: -0.03em;
        }

        .hero p {
            max-width: 640px;
            margin: 0;
            color: var(--muted);
        }

        .mulai {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
            max-width: 1180px;
            margin: 22px auto 0;
            padding: 0 clamp(16px, 4vw, 40px);
        }

        .mulai a {
            display: block;
            padding: 16px 18px;
            border: 1px solid var(--line);
            border-radius: 13px;
            background: var(--card);
            color: inherit;
            text-decoration: none;
            transition: border-color 0.16s ease, transform 0.16s ease;
        }

        .mulai a:hover {
            border-color: #93c5fd;
            transform: translateY(-2px);
        }

        .mulai strong {
            display: block;
            font-size: 15px;
        }

        .mulai span {
            font-size: 13px;
            color: var(--muted);
        }

        .layout {
            display: grid;
            grid-template-columns: 230px minmax(0, 1fr);
            gap: 32px;
            max-width: 1180px;
            margin: 28px auto 0;
            padding: 0 clamp(16px, 4vw, 40px) 64px;
        }

        .toc {
            position: sticky;
            top: 84px;
            align-self: start;
        }

        .toc p {
            margin: 0 0 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: #64748b;
        }

        .toc ul {
            margin: 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 2px;
        }

        .toc a {
            display: block;
            padding: 7px 11px;
            border-radius: 8px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }

        .toc a:hover {
            background: #e8eef7;
            color: var(--ink);
        }

        .toc .sub a {
            padding-left: 22px;
            font-size: 12.5px;
        }

        section.bagian {
            margin-bottom: 18px;
            padding: clamp(20px, 3vw, 30px);
            border: 1px solid var(--line);
            border-radius: 15px;
            background: var(--card);
        }

        section.bagian h2 {
            margin: 0 0 4px;
            font-size: 21px;
            letter-spacing: -0.02em;
        }

        section.bagian h3 {
            margin: 26px 0 6px;
            font-size: 16px;
            letter-spacing: -0.01em;
        }

        section.bagian p {
            margin: 8px 0;
        }

        .lead {
            color: var(--muted);
        }

        .peran {
            display: inline-block;
            margin-left: 8px;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            vertical-align: middle;
        }

        .peran.admin {
            background: var(--blue-soft);
            color: #1d4ed8;
        }

        .peran.petugas {
            background: var(--green-soft);
            color: var(--green);
        }

        ol.langkah {
            margin: 10px 0;
            padding: 0;
            list-style: none;
            counter-reset: langkah;
        }

        ol.langkah li {
            position: relative;
            margin: 0 0 9px;
            padding-left: 38px;
            counter-increment: langkah;
        }

        ol.langkah li::before {
            content: counter(langkah);
            position: absolute;
            left: 0;
            top: 1px;
            display: grid;
            place-items: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
        }

        ul.poin {
            margin: 8px 0;
            padding-left: 20px;
        }

        ul.poin li {
            margin-bottom: 5px;
        }

        .catatan {
            margin: 14px 0;
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid;
            font-size: 14px;
        }

        .catatan.info {
            background: var(--blue-soft);
            border-color: #bfdbfe;
        }

        .catatan.tips {
            background: var(--green-soft);
            border-color: #bbf7d0;
        }

        .catatan.awas {
            background: var(--amber-soft);
            border-color: #fde68a;
        }

        .tabel {
            overflow-x: auto;
            margin: 12px 0;
            border: 1px solid var(--line);
            border-radius: 10px;
        }

        .tabel table {
            width: 100%;
            min-width: 520px;
            border-collapse: collapse;
            font-size: 14px;
        }

        .tabel th,
        .tabel td {
            padding: 9px 13px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        .tabel th {
            background: #f8fafc;
            font-size: 12px;
            letter-spacing: 0.02em;
            color: #475569;
        }

        .tabel tr:last-child td {
            border-bottom: 0;
        }

        kbd,
        .menu {
            padding: 1px 7px;
            border-radius: 6px;
            background: #f1f5f9;
            border: 1px solid var(--line);
            font-family: inherit;
            font-size: 0.92em;
            font-weight: 600;
            white-space: nowrap;
        }

        details {
            margin: 8px 0;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
        }

        summary {
            padding: 12px 15px;
            font-weight: 600;
            cursor: pointer;
        }

        summary:focus-visible {
            outline: 2px solid var(--blue);
            outline-offset: 2px;
            border-radius: 10px;
        }

        details[open] summary {
            border-bottom: 1px solid var(--line);
        }

        details div {
            padding: 4px 15px 12px;
        }

        .alur {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 10px;
            margin: 14px 0;
            padding: 0;
            list-style: none;
        }

        .alur li {
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: #f8fafc;
            font-size: 13.5px;
        }

        .alur b {
            display: block;
            margin-bottom: 2px;
            font-size: 12px;
            color: var(--blue);
        }

        footer {
            padding: 0 clamp(16px, 4vw, 40px) 40px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        @media (max-width: 899.98px) {
            .layout {
                grid-template-columns: minmax(0, 1fr);
                gap: 14px;
            }

            .toc {
                position: static;
            }

            .toc ul {
                display: flex;
                gap: 6px;
                overflow-x: auto;
                padding-bottom: 6px;
            }

            .toc li {
                flex-shrink: 0;
            }

            .toc a {
                border: 1px solid var(--line);
                background: #fff;
            }

            .toc .sub {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            .mulai a {
                transition: none;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <span class="brand">Gudang <strong>2B</strong><small>Panduan penggunaan</small></span>
        <a class="btn-kembali" href="{{ $urlKembali }}">{{ $labelKembali }}</a>
    </header>

    <div class="hero">
        <div class="eyebrow">USER GUIDE</div>
        <h1>Panduan Penggunaan Sistem Inventaris Gudang</h1>
        <p>Pelajari cara mencatat barang masuk, barang keluar, dan peminjaman, memantau stok, hingga mencetak laporan.
            Pilih panduan sesuai peran Anda.</p>
    </div>

    <nav class="mulai" aria-label="Mulai cepat">
        <a href="#panduan-admin"><strong>Saya Administrator</strong><span>Kelola data induk, pantau dashboard, dan
                susun laporan.</span></a>
        <a href="#panduan-petugas"><strong>Saya Petugas Gudang</strong><span>Catat transaksi barang setiap hari.</span></a>
        <a href="#faq"><strong>Ada masalah?</strong><span>Jawaban untuk pertanyaan dan kendala umum.</span></a>
    </nav>

    <div class="layout">
        <aside class="toc" aria-label="Daftar isi">
            <p>DAFTAR ISI</p>
            <ul>
                <li><a href="#pengenalan">Mengenal sistem</a></li>
                <li><a href="#masuk">Masuk &amp; keluar</a></li>
                <li><a href="#kondisi">Memahami stok</a></li>
                <li><a href="#panduan-admin">Panduan Admin</a></li>
                <li class="sub"><a href="#admin-dashboard">Dashboard</a></li>
                <li class="sub"><a href="#admin-master">Data induk</a></li>
                <li class="sub"><a href="#admin-riwayat">Riwayat stok</a></li>
                <li class="sub"><a href="#admin-laporan">Laporan</a></li>
                <li><a href="#panduan-petugas">Panduan Petugas</a></li>
                <li class="sub"><a href="#petugas-masuk">Barang masuk</a></li>
                <li class="sub"><a href="#petugas-keluar">Barang keluar</a></li>
                <li class="sub"><a href="#petugas-pinjam">Peminjaman</a></li>
                <li><a href="#alur">Contoh alur</a></li>
                <li><a href="#faq">Tanya jawab</a></li>
            </ul>
        </aside>

        <main>
            <section class="bagian" id="pengenalan">
                <h2>Mengenal sistem</h2>
                <p class="lead">Gudang 2B mencatat setiap barang beserta jumlahnya menurut kondisi, dan menyimpan jejak
                    setiap perubahan stok. Angka stok tidak diubah langsung: stok berubah hanya melalui transaksi, jadi
                    selalu ada catatan siapa, kapan, dan mengapa.</p>
                <div class="tabel">
                    <table>
                        <thead>
                            <tr>
                                <th>Peran</th>
                                <th>Tugas utama</th>
                                <th>Menu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Administrator</strong></td>
                                <td>Mengelola data induk, memantau stok, dan menyusun laporan.</td>
                                <td>Dashboard, Kelola User, Kelola Supplier, Kelola Kategori, Data Induk Barang,
                                    Riwayat Stok, Laporan Gudang</td>
                            </tr>
                            <tr>
                                <td><strong>Petugas gudang</strong></td>
                                <td>Mencatat transaksi barang harian.</td>
                                <td>Dashboard, Barang Masuk, Barang Keluar, Transaksi Peminjaman, Riwayat Stok</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="catatan info">Menu yang tampil mengikuti peran akun. Membuka alamat halaman peran lain akan
                    menampilkan pesan <strong>Akses Ditolak</strong>.</div>
            </section>

            <section class="bagian" id="masuk">
                <h2>Masuk &amp; keluar</h2>
                <ol class="langkah">
                    <li>Buka halaman <span class="menu">Login</span> aplikasi.</li>
                    <li>Isi <strong>email</strong> dan <strong>password</strong> dari administrator, lalu tekan
                        tombol masuk.</li>
                    <li>Anda otomatis diarahkan ke dashboard sesuai peran: administrator ke dashboard statistik,
                        petugas ke dashboard transaksi.</li>
                    <li>Setelah selesai, tekan <span class="menu">Keluar akun</span> di bagian bawah sidebar.</li>
                </ol>
                <div class="catatan awas">Jangan membagikan password. Lupa password? Minta administrator
                    menggantinya lewat menu <strong>Kelola User</strong>.</div>
                <p>Di layar ponsel, sidebar tersembunyi. Tekan ikon tiga garis di pojok kiri atas untuk membuka menu,
                    dan tekan <kbd>Esc</kbd> atau area gelap untuk menutupnya.</p>
            </section>

            <section class="bagian" id="kondisi">
                <h2>Memahami stok</h2>
                <p class="lead">Setiap barang punya empat angka stok yang selalu berjumlah sama dengan total barang
                    yang Anda miliki.</p>
                <div class="tabel">
                    <table>
                        <thead>
                            <tr>
                                <th>Stok</th>
                                <th>Artinya</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Baik</strong></td>
                                <td>Siap dipakai, dikeluarkan, atau dipinjamkan.</td>
                            </tr>
                            <tr>
                                <td><strong>Rusak ringan</strong></td>
                                <td>Masih bisa diperbaiki. Muncul saat barang dikembalikan dalam kondisi ini.</td>
                            </tr>
                            <tr>
                                <td><strong>Rusak</strong></td>
                                <td>Tidak layak pakai. Muncul saat barang dikembalikan dalam kondisi ini.</td>
                            </tr>
                            <tr>
                                <td><strong>Dipinjam</strong></td>
                                <td>Sedang berada di peminjam dan belum dikembalikan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p><strong>Stok minimum</strong> adalah batas aman yang ditetapkan administrator. Bila stok baik sama
                    dengan atau kurang dari batas itu, barang ditandai <em>Perlu perhatian</em> atau <em>Stok
                        menipis</em> dan muncul di antrean restok petugas.</p>
            </section>

            <section class="bagian" id="panduan-admin">
                <h2>Panduan Administrator <span class="peran admin">ADMIN</span></h2>

                <h3 id="admin-dashboard">Dashboard statistik</h3>
                <p>Halaman pertama setelah login. Isinya:</p>
                <ul class="poin">
                    <li><strong>Enam kartu ringkasan:</strong> jenis barang, stok baik, sedang dipinjam, barang rusak,
                        stok menipis, dan unit masuk dalam 7 hari.</li>
                    <li><strong>Tiga grafik:</strong> barang masuk dan keluar 7 hari terakhir, komposisi stok menurut
                        kondisi, dan stok per kategori.</li>
                    <li><strong>Tiga tabel:</strong> barang perlu perhatian, peminjaman aktif, dan aktivitas stok
                        terbaru.</li>
                </ul>
                <div class="catatan info">Grafik memerlukan koneksi internet. Tanpa internet, grafik tidak tampil
                    tetapi kartu dan tabel tetap lengkap.</div>

                <h3 id="admin-master">Mengelola data induk</h3>
                <p>Siapkan data induk sebelum petugas mulai mencatat transaksi.</p>
                <div class="tabel">
                    <table>
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Yang perlu diisi</th>
                                <th>Aturan penting</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="menu">Kelola Kategori</span></td>
                                <td>Nama kategori</td>
                                <td>Nama harus unik. Kategori yang masih dipakai barang tidak bisa dihapus.</td>
                            </tr>
                            <tr>
                                <td><span class="menu">Kelola Supplier</span></td>
                                <td>Nama, telepon, email, alamat</td>
                                <td>Hanya nama yang wajib. Supplier yang sudah punya riwayat pasokan tidak bisa
                                    dihapus.</td>
                            </tr>
                            <tr>
                                <td><span class="menu">Data Induk Barang</span></td>
                                <td>Kode, nama, kategori, satuan, stok minimum, foto, keterangan</td>
                                <td>Kode barang harus unik. Foto berformat JPEG, PNG, atau WebP maksimal 2 MB. Barang
                                    yang sudah punya riwayat transaksi tidak bisa dihapus.</td>
                            </tr>
                            <tr>
                                <td><span class="menu">Kelola User</span></td>
                                <td>Nama, email, password, peran</td>
                                <td>Password minimal 6 karakter dan memuat angka. Saat mengedit, kosongkan password
                                    bila tidak ingin menggantinya. Anda tidak bisa menghapus akun sendiri.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="catatan tips">Barang baru dimulai dengan stok 0. Stok bertambah setelah petugas mencatat
                    <strong>Barang Masuk</strong>.</div>

                <h3 id="admin-riwayat">Riwayat stok</h3>
                <p>Buka <span class="menu">Riwayat Stok (Log)</span> untuk melihat setiap perubahan stok. Gunakan
                    tombol rentang cepat (<em>Hari ini</em>, <em>7 hari terakhir</em>, <em>Bulan ini</em>) atau isi
                    tanggal sendiri, lalu persempit dengan jenis transaksi, barang, petugas, atau kolom pencarian.
                    Halaman ini hanya untuk dibaca, tidak ada tombol ubah atau hapus.</p>

                <h3 id="admin-laporan">Laporan gudang</h3>
                <ol class="langkah">
                    <li>Buka <span class="menu">Laporan Gudang</span>.</li>
                    <li>Pilih jenis laporan: <strong>Stok barang</strong> (posisi stok saat ini) atau <strong>Mutasi
                            stok</strong> (perubahan stok pada periode tertentu).</li>
                    <li>Atur filter: kategori, serta tanggal dan jenis transaksi untuk laporan mutasi. Tekan
                        <strong>Tampilkan</strong>.</li>
                    <li>Tekan <strong>Cetak / PDF</strong> untuk membuka halaman siap cetak. Pilih <em>Simpan sebagai
                            PDF</em> pada jendela cetak bila ingin berkas PDF.</li>
                    <li>Tekan <strong>Unduh Excel</strong> untuk mendapatkan berkas .csv yang langsung terbuka di
                        Microsoft Excel.</li>
                </ol>
                <p>Pratinjau di layar menampilkan hingga 200 baris. Hasil cetak dan unduhan selalu memuat seluruh
                    baris.</p>
            </section>

            <section class="bagian" id="panduan-petugas">
                <h2>Panduan Petugas Gudang <span class="peran petugas">PETUGAS</span></h2>
                <p class="lead">Dashboard petugas memuat empat tombol cepat ke halaman transaksi, empat kartu aktivitas
                    Anda hari ini, serta antrean peminjaman yang belum kembali dan barang yang perlu direstok.</p>

                <h3 id="petugas-masuk">Mencatat barang masuk</h3>
                <ol class="langkah">
                    <li>Buka <span class="menu">Barang Masuk</span>.</li>
                    <li>Pilih <strong>supplier</strong> dan <strong>barang</strong>.</li>
                    <li>Isi <strong>jumlah</strong> (minimal 1) dan <strong>tanggal</strong> penerimaan.</li>
                    <li>Simpan. Stok baik barang bertambah dan nomor transaksi berawalan <kbd>BM-</kbd> dibuat
                        otomatis.</li>
                </ol>

                <h3 id="petugas-keluar">Mencatat barang keluar</h3>
                <ol class="langkah">
                    <li>Buka <span class="menu">Barang Keluar</span>.</li>
                    <li>Pilih <strong>barang</strong>. Hanya barang yang stok baiknya masih ada yang ditawarkan.</li>
                    <li>Isi <strong>jumlah</strong>, <strong>tujuan</strong> (misalnya nama ruangan atau pihak
                        penerima), dan <strong>tanggal</strong>.</li>
                    <li>Simpan. Stok baik berkurang dan nomor transaksi berawalan <kbd>BK-</kbd> dibuat otomatis.</li>
                </ol>
                <div class="catatan awas">Jumlah yang dikeluarkan tidak boleh melebihi stok baik. Sistem menolak dan
                    menampilkan stok yang tersedia.</div>

                <h3 id="petugas-pinjam">Peminjaman dan pengembalian</h3>
                <p><strong>Mencatat peminjaman</strong></p>
                <ol class="langkah">
                    <li>Buka <span class="menu">Transaksi Peminjaman</span>.</li>
                    <li>Pilih <strong>barang</strong>, isi <strong>nama peminjam</strong> dan
                        <strong>jumlah</strong>.</li>
                    <li>Simpan. Stok baik berpindah menjadi stok dipinjam, tanggal pinjam terisi otomatis, dan nomor
                        berawalan <kbd>PM-</kbd> dibuat.</li>
                </ol>
                <p><strong>Mencatat pengembalian</strong></p>
                <ol class="langkah">
                    <li>Pada daftar peminjaman, cari transaksi berstatus <em>dipinjam</em>.</li>
                    <li>Pilih <strong>kondisi saat dikembalikan</strong>: baik, rusak ringan, atau rusak.</li>
                    <li>Simpan. Stok dipinjam berkurang dan bertambah pada kondisi yang Anda pilih.</li>
                </ol>
                <div class="catatan awas">Satu peminjaman hanya dapat dikembalikan satu kali. Periksa kondisi barang
                    dengan teliti sebelum memilih, karena kondisi menentukan di stok mana barang kembali.</div>

                <h3>Melihat riwayat</h3>
                <p>Menu <span class="menu">Riwayat Stok (Log)</span> menampilkan seluruh perubahan stok beserta
                    petugasnya. Gunakan filter tanggal dan jenis transaksi untuk mencari catatan tertentu.</p>
            </section>

            <section class="bagian" id="alur">
                <h2>Contoh alur dari awal sampai akhir</h2>
                <ol class="alur">
                    <li><b>1 · ADMIN</b>Membuat kategori, supplier, dan data barang.</li>
                    <li><b>2 · PETUGAS</b>Mencatat barang masuk. Stok baik bertambah.</li>
                    <li><b>3 · PETUGAS</b>Meminjamkan barang. Stok baik pindah ke dipinjam.</li>
                    <li><b>4 · PETUGAS</b>Menerima pengembalian dan memilih kondisi barang.</li>
                    <li><b>5 · ADMIN</b>Memantau dashboard dan mencetak laporan mutasi.</li>
                </ol>
            </section>

            <section class="bagian" id="faq">
                <h2>Tanya jawab</h2>
                <details>
                    <summary>Muncul pesan "Akses Ditolak". Apa penyebabnya?</summary>
                    <div>
                        <p>Halaman tersebut khusus peran lain. Administrator tidak membuka halaman petugas, dan
                            sebaliknya. Gunakan menu di sidebar, atau masuk dengan akun yang sesuai.</p>
                    </div>
                </details>
                <details>
                    <summary>Mengapa barang keluar atau peminjaman ditolak karena stok tidak mencukupi?</summary>
                    <div>
                        <p>Hanya stok baik yang boleh dikeluarkan atau dipinjamkan. Barang yang sedang dipinjam, rusak
                            ringan, atau rusak tidak ikut terhitung. Periksa angkanya di dashboard atau laporan stok.
                        </p>
                    </div>
                </details>
                <details>
                    <summary>Saya salah memasukkan transaksi. Bisakah diubah atau dihapus?</summary>
                    <div>
                        <p>Tidak. Catatan transaksi sengaja dikunci agar jejak audit tetap utuh. Hubungi administrator
                            dan jelaskan transaksi mana yang keliru.</p>
                    </div>
                </details>
                <details>
                    <summary>Grafik di dashboard tidak muncul.</summary>
                    <div>
                        <p>Grafik dimuat dari internet. Periksa koneksi lalu muat ulang halaman. Angka pada kartu dan
                            tabel tetap benar walaupun grafik tidak tampil.</p>
                    </div>
                </details>
                <details>
                    <summary>Barang tidak bisa dihapus.</summary>
                    <div>
                        <p>Barang, supplier, atau kategori yang sudah dipakai di data lain tidak bisa dihapus supaya
                            riwayat tidak rusak. Itu perilaku yang disengaja.</p>
                    </div>
                </details>
                <details>
                    <summary>File Excel terbuka dalam satu kolom.</summary>
                    <div>
                        <p>Berkas .csv memakai pemisah titik koma. Buka dengan Microsoft Excel melalui klik ganda,
                            bukan lewat impor teks. Bila tetap bergabung, pilih <em>Data → Text to Columns</em> dan
                            gunakan pemisah titik koma.</p>
                    </div>
                </details>
            </section>
        </main>
    </div>

    <footer>Gudang 2B · Sistem Inventaris Barang Gudang · Kelompok 2B</footer>
</body>

</html>
