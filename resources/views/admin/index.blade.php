@extends('layouts.app')

@section('title', 'Admin CMS')

@section('content')
<div class="bg-neutral-900/40 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-xl relative">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-neutral-800 pb-5 mb-5 gap-3">
        <div>
            <h3 class="text-lg font-serif italic text-white flex items-center gap-2">Konsol Manajemen CMS (CRUD)</h3>
            <p class="text-xs text-neutral-450 mt-1 font-serif">Gunakan konsol terpadu ini untuk menulis, mengedit, mencari, dan menghapus artikel siber di sistem secara langsung.</p>
        </div>
        <a href="{{ route('admin.articles.create') }}" class="flex items-center gap-2 bg-blue-650 hover:bg-blue-600 text-white font-bold py-2.5 px-4 rounded text-xs uppercase tracking-wider transition-all shadow-md cursor-pointer">Tambah Artikel Baru</a>
    </div>

    <div class="space-y-4 font-mono">
        <form method="GET" action="{{ route('admin.index') }}" class="flex items-center bg-neutral-950 border border-neutral-800 rounded px-3 py-1.5 w-full sm:w-80">
            <span class="text-neutral-500 text-xs">SEARCH</span>
            <input name="q" type="text" placeholder="Cari artikel (Judul/Kategori/Penulis)..." class="w-full bg-transparent text-neutral-150 placeholder:text-neutral-700 text-xs py-1 px-2.5 focus:outline-none font-sans" value="{{ $search }}">
        </form>
        <div class="overflow-x-auto rounded-xl border border-neutral-800 bg-neutral-950/40">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-neutral-950 text-neutral-500 uppercase tracking-widest text-[9.5px] border-b border-neutral-800 font-bold">
                    <tr><th class="p-3.5">Judul Berita Siber</th><th class="p-3.5">Kategori</th><th class="p-3.5">Penulis / Tanggal</th><th class="p-3.5 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/65">
                    @forelse($articles as $article)
                        <tr class="hover:bg-neutral-900/30 transition-colors">
                            <td class="p-3.5 font-sans font-bold text-neutral-200">
                                <div class="max-w-xs md:max-w-sm truncate text-sm font-serif italic" title="{{ $article->title }}">{{ $article->title }}</div>
                                <span class="text-[10.5px] text-neutral-505 block font-normal mt-0.5 truncate max-w-xs md:max-w-sm font-serif">{{ $article->summary }}</span>
                            </td>
                            <td class="p-3.5"><span class="bg-neutral-950 text-neutral-400 border border-neutral-800 rounded px-2 py-0.5 text-[9.5px] font-mono tracking-wider uppercase font-bold">{{ $article->category }}</span></td>
                            <td class="p-3.5 font-sans text-neutral-300"><div class="font-semibold text-[11px]">{{ $article->author }}</div><div class="text-neutral-500 text-[10px] font-mono mt-0.5">{{ $article->published_date?->format('Y-m-d') }}</div></td>
                            <td class="p-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('articles.show', $article) }}" class="bg-neutral-950 hover:bg-neutral-850 border border-neutral-800 hover:border-neutral-700 text-neutral-400 hover:text-blue-400 px-3 py-2 rounded transition-all cursor-pointer">Lihat</a>
                                    <a href="{{ route('admin.articles.edit', $article) }}" class="bg-neutral-950 hover:bg-neutral-850 border border-neutral-800 hover:border-neutral-700 text-neutral-400 hover:text-blue-400 px-3 py-2 rounded transition-all cursor-pointer">Edit</a>
                                    <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel siber: {{ addslashes($article->title) }}? Tindakan ini tidak dapat dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-neutral-950 hover:bg-neutral-850 border border-neutral-800 hover:border-red-900 text-neutral-400 hover:text-red-400 px-3 py-2 rounded transition-all cursor-pointer">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-6 text-center text-neutral-550 italic font-serif">Tidak ada artikel yang cocok dengan parameter pencarian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
