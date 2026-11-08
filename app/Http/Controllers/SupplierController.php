<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::latest()->get();
        return view('admin.supplier', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'telepon'       => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'alamat'        => 'nullable|string',
        ]);

        Supplier::create($request->all());

        return redirect('/admin/supplier')->with('success', 'Supplier berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'telepon'       => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'alamat'        => 'nullable|string',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());

        return redirect('/admin/supplier')->with('success', 'Data supplier berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        // Mencegah hapus supplier jika sudah ada riwayat pasokan barang
        if ($supplier->barangMasuk()->count() > 0) {
            return redirect('/admin/supplier')->with('error', 'Supplier tidak dapat dihapus karena memiliki riwayat pasokan barang!');
        }

        $supplier->delete();
        return redirect('/admin/supplier')->with('success', 'Supplier berhasil dihapus!');
    }
}