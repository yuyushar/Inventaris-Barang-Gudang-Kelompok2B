<aside class="tx-stock-panel" data-tx-stock-panel aria-live="polite">
    <div class="tx-stock-panel-head">
        <span class="fe-eyebrow mb-0">CEK KETERSEDIAAN STOK</span>
        <span class="fe-badge fe-badge-gray" data-tx-stock-status>Pilih barang</span>
    </div>
    <p class="tx-stock-name" data-tx-stock-name>Belum ada barang dipilih.</p>
    <dl class="tx-stock-grid">
        <div>
            <dt>Stok baik tersedia</dt>
            <dd data-tx-stock-available>—</dd>
        </div>
        <div>
            <dt>{{ $mode === 'peminjaman' ? 'Dipinjam' : 'Dikeluarkan' }}</dt>
            <dd data-tx-stock-request>—</dd>
        </div>
        <div>
            <dt>Sisa stok baik</dt>
            <dd data-tx-stock-remaining>—</dd>
        </div>
        @if($mode === 'keluar')
        <div>
            <dt>Stok minimum</dt>
            <dd data-tx-stock-minimum>—</dd>
        </div>
        @endif
    </dl>
    <div class="tx-stock-meter" aria-hidden="true"><span data-tx-stock-meter></span></div>
    <p class="tx-stock-note" data-tx-stock-note>Jumlah tidak boleh melebihi stok baik yang tersedia.</p>
</aside>
