<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $penerbit = $request->input('penerbit');

        $listPenerbit = Buku::select('penerbit')->distinct()->pluck('penerbit');

        $buku = Buku::when($search, function ($q, $search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('judul', 'like', "%{$search}%")
                            ->orWhere('penulis', 'like', "%{$search}%");
                    });
                })
                ->when($penerbit, function ($q, $penerbit) {
                    $q->where('penerbit', $penerbit);
                })
                ->latest()
                ->paginate(5)
                ->withQueryString();

        return view('buku.index', compact('buku', 'search', 'listPenerbit'));
    }

    public function create()
    {
        return view('buku.create');
    }

    // TODO 4
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'        => 'required|string|max:255',
            'penulis'      => 'required|string|max:255',
            'penerbit'     => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|min:1000|max:' . date('Y'),
            'stok'         => 'required|integer|min:0',
            'cover'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('buku', 'public');
        }

        Buku::create($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Buku $buku)
    {
        return view('buku.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        return view('buku.edit', compact('buku'));
    }

    // TODO 5
    public function update(Request $request, Buku $buku)
    {
        $validated = $request->validate([
            'judul'        => 'required|string|max:255',
            'penulis'      => 'required|string|max:255',
            'penerbit'     => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|min:1000|max:' . date('Y'),
            'stok'         => 'required|integer|min:0',
            'cover'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('cover')) {
            if ($buku->cover) {
                Storage::disk('public')->delete($buku->cover);
            }
            $validated['cover'] = $request->file('cover')->store('buku', 'public');
        }

        $buku->update($validated);

        return redirect()->route('buku.index')->with('success', 'Buku berhasil diperbarui.');
    }

    // TODO 6
    public function destroy(Buku $buku)
    {
        if ($buku->cover) {
            Storage::disk('public')->delete($buku->cover);
        }

        $buku->delete();

        return redirect()->route('buku.index')->with('success', 'Buku berhasil dihapus.');
    }
}