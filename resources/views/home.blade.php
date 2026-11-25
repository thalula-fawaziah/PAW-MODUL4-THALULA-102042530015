@extends('layouts.app')

@section('title', 'Katalog Buku Perpustakaan')

@push('styles')
<style>
    .book-card:hover { transform: translateY(-4px); transition: transform 0.2s ease; }
    .book-cover { height: 240px; object-fit: cover; width: 100%; }
    .book-cover-placeholder {
        height: 240px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #e9ecef;
        color: #adb5bd;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="container py-5">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header & Form Pencarian --}}
    <div class="text-center mb-5">
        <h1 class="fw-bold">Katalog Koleksi Buku</h1>
        <p class="text-muted">Temukan dan pinjam buku referensi perkuliahan terbaikmu</p>

         {{-- TODO 10: Lengkapi form pencarian, filter status/penerbit, dan sorting di bawah ini --}}
        <div class="row justify-content-center mt-4">
            <div class="col-lg-11">
                <form action="{{ url()->current() }}" method="GET" class="card shadow-sm border-0 p-3 bg-light text-start">
                    <div class="row g-2 align-items-center">

                        {{-- Input Pencarian --}}
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0"
                                       placeholder="Cari judul, penulis..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>

                        {{-- Filter Penerbit --}}
                        <div class="col-md-3">
                            <select name="penerbit" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Penerbit</option>
                                @foreach($listPenerbit as $p)
                                    <option value="{{ $p }}" {{ request('penerbit') === $p ? 'selected' : '' }}>
                                        {{ $p }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Ketersediaan --}}
                        <div class="col-md-2">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="tersedia" {{ request('status') === 'tersedia' ? 'selected' : '' }}>
                                    Stok Tersedia
                                </option>
                                <option value="habis" {{ request('status') === 'habis' ? 'selected' : '' }}>
                                    Stok Habis
                                </option>
                            </select>
                        </div>

                        {{-- Sort / Urutkan --}}
                        <div class="col-md-2">
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="">Urutkan: Terbaru</option>
                                <option value="judul_asc" {{ request('sort') === 'judul_asc' ? 'selected' : '' }}>Judul (A - Z)</option>
                                <option value="judul_desc" {{ request('sort') === 'judul_desc' ? 'selected' : '' }}>Judul (Z - A)</option>
                                <option value="tahun_desc" {{ request('sort') === 'tahun_desc' ? 'selected' : '' }}>Tahun (Terbaru)</option>
                                <option value="tahun_asc" {{ request('sort') === 'tahun_asc' ? 'selected' : '' }}>Tahun (Terlama)</option>
                                <option value="stok_desc" {{ request('sort') === 'stok_desc' ? 'selected' : '' }}>Stok Terbanyak</option>
                            </select>
                        </div>

                        {{-- Tombol Reset / Submit --}}
                        <div class="col-md-1 d-flex">
                            @if(request('search') || request('penerbit') || request('status') || request('sort'))
                                <a href="{{ url()->current() }}" class="btn btn-outline-danger w-100" title="Reset Semua Filter">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            @else
                                <button class="btn btn-primary w-100" type="submit" title="Terapkan">
                                    <i class="bi bi-funnel"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Info Hasil Filter --}}
        @if(request('search') || request('penerbit') || request('status') || request('sort'))
            <p class="text-muted mt-3 small">
                Menampilkan hasil filter:
                @if(request('search')) pencarian <strong>"{{ request('search') }}"</strong>@endif
                @if(request('penerbit')) &bull; penerbit <strong>"{{ request('penerbit') }}"</strong>@endif
                @if(request('status')) &bull; status <strong>"{{ request('status') === 'tersedia' ? 'Stok Tersedia' : 'Stok Habis' }}"</strong>@endif
                @if(request('sort')) &bull; urutan <strong>"{{ request('sort') }}"</strong>@endif
                &mdash; ditemukan <strong>{{ $buku->total() }}</strong> buku
            </p>
        @endif
    </div>

    {{-- Grid Katalog Buku --}}
    <div class="row">
        @forelse($buku as $item)
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0 book-card">
                @if($item->cover)
                    <img src="{{ asset('storage/' . $item->cover) }}" class="card-img-top book-cover" alt="{{ $item->judul }}">
                @else
                    <div class="book-cover-placeholder">
                        <div class="text-center">
                            <i class="bi bi-book fs-1 d-block mb-1"></i>
                            <span>Tidak Ada Sampul</span>
                        </div>
                    </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-bold text-truncate mb-1" title="{{ $item->judul }}">{{ $item->judul }}</h6>
                    <p class="card-text text-muted small mb-2">{{ $item->penulis }}</p>
                    <div class="mt-auto">
                        <span class="badge {{ $item->stok > 0 ? 'bg-success' : 'bg-danger' }} mb-2">
                            {{ $item->stok > 0 ? 'Tersedia (' . $item->stok . ')' : 'Stok Kosong' }}
                        </span>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                    <a href="{{ route('buku.publik.show', $item->id) }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-eye me-1"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
            <p class="text-muted fs-5">Buku tidak ditemukan.</p>
            @if(request('search'))
                <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm">Lihat Semua Buku</a>
            @endif
        </div>
        @endforelse
    </div>

    {{-- Paginasi --}}
    @if($buku->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $buku->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection