<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    // Menampilkan daftar kategori
    public function index()
    {
        $kategoris = Kategori::latest()->get();
        return view('admin.kategori', compact('kategoris'));
    }

    // Menyimpan kategori baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|unique:kategori,nama_kategori|max:255',
            'deskripsi'     => 'nullable|string',
        ]);

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi'     => $request->deskripsi,
        ]);

        return redirect('/admin/kategori')->with('success', 'Kategori berhasil ditambahkan!');
    }

    // Mengubah data kategori
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kategori' => 'required|max:255|unique:kategori,nama_kategori,' . $id,
            'deskripsi'     => 'nullable|string',
        ]);

        $kategori = Kategori::findOrFail($id);
        $kategori->update([
            'nama_kategori' => $request->nama_kategori,
            'deskripsi'     => $request->deskripsi,
        ]);

        return redirect('/admin/kategori')->with('success', 'Kategori berhasil diperbarui!');
    }

    // Menghapus kategori
    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        
        // Cek jika kategori masih dipakai oleh barang
        if ($kategori->barang()->count() > 0) {
            return redirect('/admin/kategori')->with('error', 'Kategori tidak dapat dihapus karena masih digunakan oleh data barang!');
        }

        $kategori->delete();
        return redirect('/admin/kategori')->with('success', 'Kategori berhasil dihapus!');
    }
}