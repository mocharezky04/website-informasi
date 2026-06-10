@php($fallbackImage = 'data:image/svg+xml;utf8,'.urlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675"><rect width="1200" height="675" fill="#0a0a0a"/><rect x="80" y="80" width="1040" height="515" rx="24" fill="#111827" stroke="#1f2937"/><text x="110" y="180" fill="#93c5fd" font-family="Arial, sans-serif" font-size="42" font-weight="700">Website Informasi</text><text x="110" y="240" fill="#9ca3af" font-family="Arial, sans-serif" font-size="24">Gambar artikel tidak tersedia</text></svg>'))
<div class="space-y-5 max-w-full overflow-hidden">
    <div class="aspect-[16/9] md:max-h-[320px] w-full bg-neutral-950 border border-neutral-800 rounded-xl overflow-hidden relative">
        <div class="absolute top-3 left-3 z-10 bg-neutral-950/90 text-neutral-300 text-[9px] border border-neutral-800 px-3 py-1 rounded font-bold uppercase tracking-wider font-mono">{{ $article->category }}</div>
        <img
            src="{{ $article->image_url ?: $fallbackImage }}"
            onerror="this.onerror=null;this.src='{{ $fallbackImage }}';"
            alt="{{ $article->title }}"
            referrerpolicy="no-referrer"
            loading="lazy"
            class="w-full h-full object-cover"
        >
    </div>
    <div class="space-y-2">
        <div class="flex flex-wrap items-center gap-3 text-neutral-400 text-xs font-mono">
            <span>Oleh: {{ $article->author }}</span>
            <span class="text-neutral-700">|</span>
            <span>Diterbitkan: {{ $article->published_date?->format('Y-m-d') }}</span>
        </div>
    </div>
    <hr class="border-neutral-800">
    <div class="markdown-body space-y-4 text-neutral-300 font-serif leading-relaxed text-[13px] sm:text-sm break-words max-w-full overflow-hidden">
        {!! \App\Support\MarkdownRenderer::render($article->content) !!}
    </div>
    <div class="flex flex-wrap gap-2 pt-4 border-t border-neutral-800 mt-6">
        @foreach($article->tags ?? [] as $tag)
            <span class="text-[10px] font-mono bg-neutral-950 text-neutral-500 border border-neutral-800 rounded px-2.5 py-0.5">#{{ $tag }}</span>
        @endforeach
    </div>
</div>
