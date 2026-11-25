<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $stok     = $request->input('stok');     // tersedia / habis
        $sort     = $request->input('sort');     // judul / tahun / stok
        $penerbit = $request->input('penerbit'); // filter penerbit

        // Ambil daftar penerbit unik untuk dropdown di home.blade.php
        $listPenerbit = Buku::select('penerbit')
                            ->whereNotNull('penerbit')
                            ->distinct()
                            ->pluck('penerbit');

        $buku = Buku::when($search, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('judul', 'like', "%{$search}%")
                          ->orWhere('penulis', 'like', "%{$search}%");
                    });
                })
                ->when($penerbit, fn ($q, $penerbit) => $q->where('penerbit', $penerbit))
                ->when($stok === 'tersedia', fn ($q) => $q->where('stok', '>', 0))
                ->when($stok === 'habis', fn ($q) => $q->where('stok', '<=', 0))
                ->when($sort, function ($q, $sort) {
                    return match ($sort) {
                        'judul' => $q->orderBy('judul', 'asc'),
                        'tahun' => $q->orderBy('tahun_terbit', 'desc'),
                        'stok'  => $q->orderBy('stok', 'desc'),
                        default => $q->latest(),
                    };
                }, fn ($q) => $q->latest())
                ->paginate(5)
                ->withQueryString();

        return view('home', compact('buku', 'search', 'stok', 'sort', 'listPenerbit'));
    }

    public function show(Buku $buku)
    {
        return view('home_show', compact('buku'));
    }
}