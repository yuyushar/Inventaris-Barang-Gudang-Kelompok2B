<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index()
    {
        // Mengambil data barang beserta relasi kategorinya
        $barangs = Barang::with('kategori')->latest()->get();
        $kategoris = Kategori::all();

        return view('admin.barang', compact('barangs', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|unique:barang,kode_barang|max:50',
            'nama_barang' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'satuan' => 'required|string|max:50',
            'stok_minimum' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('barang', 'public');
        }

        Barang::create($validated);

        return redirect('/admin/barang')->with('success', 'Data barang berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);

        $validated = $request->validate([
            'kode_barang' => 'required|string|max:50|unique:barang,kode_barang,'.$id,
            'nama_barang' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'satuan' => 'required|string|max:50',
            'stok_minimum' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar'] = $request->file('gambar')->store('barang', 'public');
        }

        $barang->update($validated);

        return redirect('/admin/barang')->with('success', 'Data barang berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);

        if ($barang->peminjaman()->count() > 0 || $barang->riwayatStok()->count() > 0) {
            return redirect('/admin/barang')->with('error', 'Barang tidak dapat dihapus karena memiliki riwayat transaksi!');
        }

        $barang->delete();

        return redirect('/admin/barang')->with('success', 'Data barang berhasil dihapus!');
    }
}
