@php
    $barangTerpilih = (string) ($baris['barang_id'] ?? '');
    $jumlahLama = $baris['jumlah'] ?? '';
    $errorBarang = is_int($indeks) ? $errors->first('items.'.$indeks.'.barang_id') : null;
    $errorJumlah = is_int($indeks) ? $errors->first('items.'.$indeks.'.jumlah') : null;
@endphp
<tr class="tx-item-row" data-tx-item-row>
    <td class="tx-item-no" data-tx-item-no>{{ $nomor }}</td>
    <td class="tx-item-barang">
        <div data-tx-field>
            <label class="visually-hidden" for="items_{{ $indeks }}_barang_id">Barang baris {{ $nomor }}</label>
            <select id="items_{{ $indeks }}_barang_id" name="items[{{ $indeks }}][barang_id]"
                class="form-select {{ $errorBarang ? 'is-invalid' : '' }}" data-tx-barang data-label="Barang" required>
                <option value="">Pilih barang</option>
                @foreach($barangs as $barang)
                <option value="{{ $barang->id }}" data-stok="{{ $barang->stok_baik }}" data-satuan="{{ $barang->satuan }}"
                    data-nama="{{ $barang->nama_barang }}" data-kode="{{ $barang->kode_barang }}"
                    @selected($barangTerpilih === (string) $barang->id)>{{ $barang->kode_barang }} · {{ $barang->nama_barang }}</option>
                @endforeach
            </select>
            @if($errorBarang)
            <div class="tx-field-error" role="alert">{{ $errorBarang }}</div>
            @endif
        </div>
    </td>
    <td class="tx-item-stok tx-item-stok-sekarang" data-caption="Stok saat ini"><span class="tx-stock-pill"
            data-tx-stok-sekarang>—</span></td>
    <td class="tx-item-jumlah" data-caption="Jumlah masuk">
        <div data-tx-field>
            <label class="visually-hidden" for="items_{{ $indeks }}_jumlah">Jumlah masuk baris {{ $nomor }}</label>
            <input id="items_{{ $indeks }}_jumlah" name="items[{{ $indeks }}][jumlah]" type="number"
                class="form-control {{ $errorJumlah ? 'is-invalid' : '' }}" min="1" step="1" inputmode="numeric"
                placeholder="0" value="{{ $jumlahLama }}" data-tx-jumlah data-label="Jumlah masuk" required>
            @if($errorJumlah)
            <div class="tx-field-error" role="alert">{{ $errorJumlah }}</div>
            @endif
        </div>
    </td>
    <td class="tx-item-stok tx-item-stok-setelah" data-caption="Stok setelah"><span class="tx-stock-pill"
            data-tx-stok-setelah>—</span></td>
    <td class="tx-item-aksi">
        <button type="button" class="btn fe-btn-danger" data-tx-remove-row
            aria-label="Hapus baris {{ $nomor }}">Hapus</button>
    </td>
</tr>
