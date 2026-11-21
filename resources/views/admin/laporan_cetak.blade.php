<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $laporan['judul'] }} · Gudang 2B</title>
    @php
        $kolomAngka = ['No', 'Jumlah', 'Stok baik', 'Rusak ringan', 'Rusak', 'Dipinjam', 'Total', 'Stok minimum'];
    @endphp
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            font-family: "Segoe UI", Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            padding: 12px 16px;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            background: #eff6ff;
            font-size: 13px;
        }

        .toolbar button,
        .toolbar a {
            display: inline-block;
            padding: 8px 16px;
            border: 0;
            border-radius: 8px;
            font: inherit;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button {
            background: #2563eb;
            color: #fff;
        }

        .toolbar a {
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .kop {
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 2px solid #192432;
        }

        .kop small {
            display: block;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 600;
        }

        .kop h1 {
            margin: 4px 0 2px;
            font-size: 20px;
        }

        .kop p {
            margin: 0;
            color: #475569;
        }

        .ringkasan {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 14px;
        }

        .ringkasan div {
            min-width: 120px;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .ringkasan strong {
            display: block;
            font-size: 16px;
        }

        .ringkasan span {
            font-size: 11px;
            color: #64748b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 5px 7px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
            overflow-wrap: anywhere;
        }

        th {
            background: #f1f5f9;
            text-align: left;
            font-size: 11px;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        .angka {
            text-align: right;
        }

        .kosong {
            padding: 24px;
            text-align: center;
            color: #64748b;
        }

        .tanda-tangan {
            display: flex;
            justify-content: flex-end;
            margin-top: 28px;
            page-break-inside: avoid;
        }

        .tanda-tangan div {
            width: 240px;
            text-align: center;
        }

        .tanda-tangan .ruang {
            height: 64px;
        }

        @media print {
            body {
                padding: 0;
            }

            .toolbar {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <span>Pilih <strong>Simpan sebagai PDF</strong> pada jendela cetak untuk membuat berkas PDF.</span>
        <span>
            <a href="{{ route('admin.laporan.index', request()->query()) }}">Kembali</a>
            <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        </span>
    </div>

    <header class="kop">
        <small>GUDANG 2B · SISTEM INVENTARIS</small>
        <h1>{{ $laporan['judul'] }}</h1>
        <p>{{ $laporan['periode'] }}@foreach($laporan['filter'] as $keterangan) · {{ $keterangan }}@endforeach</p>
    </header>

    <section class="ringkasan" aria-label="Ringkasan">
        @foreach($laporan['ringkasan'] as $kartu)
        <div><strong>{{ number_format($kartu['nilai'], 0, ',', '.') }}</strong><span>{{ $kartu['label'] }}</span></div>
        @endforeach
    </section>

    @if(count($laporan['baris']) === 0)
    <p class="kosong">Tidak ada data untuk laporan ini.</p>
    @else
    <table>
        <thead>
            <tr>
                @foreach($laporan['kolom'] as $kolom)
                <th @class(['angka' => in_array($kolom, $kolomAngka, true)])>{{ $kolom }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($laporan['baris'] as $baris)
            <tr>
                @foreach($baris as $indeks => $sel)
                <td @class(['angka' => in_array($laporan['kolom'][$indeks], $kolomAngka, true)])>{{ is_int($sel) && $laporan['kolom'][$indeks] !== 'No' ? number_format($sel, 0, ',', '.') : $sel }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="tanda-tangan">
        <div>
            <p>Dicetak {{ $waktuCetak->format('d/m/Y H:i') }}</p>
            <div class="ruang"></div>
            <strong>{{ $pencetak }}</strong><br>
            <small>Administrator</small>
        </div>
    </div>
</body>

</html>
