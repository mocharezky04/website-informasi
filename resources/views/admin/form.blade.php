@extends('layouts.app')

@section('title', $isEdit ? 'Edit Artikel Siber' : 'Tambah Artikel Siber')

@section('content')
@php
    $initialTagsInput = old('tags_input', implode(', ', $article->tags ?: []));
    $activeAiProvider = config('services.ai.provider', 'gemini');
    $activeAiLabel = $activeAiProvider === 'openrouter' ? 'OpenRouter / Auto' : 'Gemini Flash';
@endphp
<meta name="openrouter-key" content="{{ config('services.openrouter.api_key') }}">
<meta name="openrouter-categories" content="{{ implode(',', \App\Http\Controllers\ArticleController::CATEGORIES) }}">
<div class="bg-neutral-900/40 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-xl relative" x-data="adminArticleForm()">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-neutral-800 pb-5 mb-5 gap-3">
        <div>
            <h3 class="text-lg font-serif italic text-white">{{ $isEdit ? 'Edit Advis Keamanan' : 'Tulis Artikel Siber Baru' }}</h3>
            <p class="text-xs text-neutral-450 mt-1 font-serif">Form artikel mendukung Markdown, gambar eksternal, upload gambar, tag, dan asisten draf {{ $activeAiLabel }}.</p>
        </div>
        <a href="{{ route('admin.index') }}" class="flex items-center gap-1.5 bg-neutral-950 hover:bg-neutral-850 text-neutral-400 hover:text-white py-2 px-4 rounded text-xs uppercase tracking-wider font-bold border border-neutral-800 transition-all cursor-pointer font-mono">Kembali ke Daftar</a>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded p-3 font-mono mb-5">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $isEdit ? route('admin.articles.update', $article) : route('admin.articles.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="bg-neutral-950 border border-blue-500/15 rounded-2xl p-5">
            <div class="flex items-center gap-1.5 text-blue-400 text-xs font-bold mb-1.5 uppercase tracking-wider font-mono">Asisten Penulisan Artikel Otomatis AI ({{ $activeAiLabel }})</div>
            <p class="text-yellow-500/70 text-[10px] font-mono mb-3.5 leading-relaxed">⚠️ Disclaimer: Proses pembuatan draf AI dapat memakan waktu lebih lama dari biasanya dikarenakan server hosting gratis memiliki keterbatasan koneksi. Harap bersabar.</p>
            <p class="text-[11.5px] text-neutral-450 mb-3.5 leading-relaxed font-serif">Ketik topik keamanan defensif siber yang diinginkan. Boleh sekaligus tulis arahan gambar, misalnya: "DDoS Cloudflare, gambar dashboard network traffic gelap".</p>
            <div class="flex flex-col sm:flex-row gap-2 font-mono">
                <input type="text" placeholder="Contoh: ransomware registry windows, gambar forensik gelap" class="flex-1 bg-neutral-900 border border-neutral-800 focus:border-blue-500/50 rounded px-3 py-2 text-xs text-white focus:outline-none font-sans" x-model="aiKeyword">
                <button type="button" @click="generateDraft()" :disabled="aiLoading" class="bg-blue-600/10 hover:bg-blue-600/20 text-blue-400 border border-blue-500/30 hover:border-blue-400/50 font-black px-4 py-2 rounded text-xs uppercase tracking-wider transition-all disabled:opacity-40 cursor-pointer" x-text="aiLoading ? 'Menyusun...' : 'Tulis Draf AI'"></button>
            </div>
            <p x-show="aiError" x-cloak class="text-red-400 text-[11px] font-mono mt-2.5" x-text="'Error: ' + aiError"></p>
            <p x-show="aiSuccess" x-cloak class="text-blue-400 text-[11px] font-mono mt-2.5" x-text="aiSuccess"></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-neutral-400 mb-1.5 uppercase tracking-wider font-mono">Judul Artikel <span class="text-red-500">*</span></label>
                <input name="title" x-model="title" type="text" required class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-2 px-3 text-sm text-white placeholder:text-neutral-700 focus:outline-none" placeholder="Masukkan judul ulasan keamanan siber...">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-neutral-400 mb-1.5 uppercase tracking-wider font-mono">Ringkasan Berita <span class="text-red-500">*</span></label>
                <textarea name="summary" x-model="summary" required rows="2" class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-2 px-3 text-xs text-white placeholder:text-neutral-700 focus:outline-none font-serif" placeholder="Deskripsi singkat yang atraktif untuk halaman depan..."></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-neutral-400 mb-1.5 uppercase tracking-wider font-mono">Kategori Berita <span class="text-red-500">*</span></label>
                <select name="category" x-model="category" class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-2 px-3 text-xs text-white focus:outline-none">
                    @foreach(\App\Http\Controllers\ArticleController::CATEGORIES as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-neutral-400 mb-1.5 uppercase tracking-wider font-mono">Nama Penulis / Author</label>
                <input name="author" x-model="author" type="text" class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-2 px-3 text-xs text-white placeholder:text-neutral-700 focus:outline-none" placeholder="Defaults: SOC Team Contributor">
            </div>
            <div class="md:col-span-2 space-y-2">
                <label class="block text-xs font-bold text-neutral-400 uppercase tracking-wider font-mono">Gambar Utama Berita siber</label>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start bg-neutral-950/60 p-4 border border-neutral-850 rounded-xl">
                    <div class="md:col-span-3 flex flex-col items-center justify-center bg-neutral-950 border border-neutral-800 rounded-lg p-2 h-28 overflow-hidden relative">
                        <img x-show="imageUrl" :src="imageUrl" alt="Pratinjau Gambar" referrerpolicy="no-referrer" class="w-full h-full object-cover rounded">
                        <div x-show="!imageUrl" class="text-center text-[10px] text-neutral-600 font-mono">Belum ada<br>gambar</div>
                    </div>
                    <div class="md:col-span-9 space-y-3">
                        <div>
                            <span class="block text-[9px] font-mono text-neutral-400 mb-1.5 uppercase tracking-wider font-bold">Metode 1: Unggah Gambar (.jpg, .jpeg, .png, .gif, .webp)</span>
                            <input name="image_file" type="file" accept="image/*" class="block w-full text-[10px] text-neutral-400 file:bg-blue-650/10 file:text-blue-400 file:border file:border-blue-500/30 file:rounded file:px-3 file:py-1.5 file:font-mono file:font-bold file:uppercase file:tracking-wider">
                            <p class="text-[10px] text-neutral-500 mt-1 font-mono">Batas file max 5MB. Upload tersimpan ke /public/uploads/.</p>
                        </div>
                        <div class="relative flex py-1 items-center"><div class="flex-grow border-t border-neutral-850"></div><span class="flex-shrink mx-2 text-[9px] text-neutral-500 font-mono">ATAU</span><div class="flex-grow border-t border-neutral-850"></div></div>
                        <div>
                            <span class="block text-[9px] font-mono text-neutral-400 mb-1.5 uppercase tracking-wider font-bold">Metode 2: Tempel Link URL Gambar Eksternal</span>
                            <input name="image_url" x-model="imageUrl" type="text" class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-1.5 px-3 text-xs text-white placeholder:text-neutral-700 focus:outline-none" placeholder="Contoh: https://images.unsplash.com/photo-...">
                            <p x-show="imageSuggestion && !imageUrl" x-cloak class="text-[10px] text-blue-400 mt-2 font-mono">
                                Saran pencarian gambar: <span x-text="imageSuggestion"></span>
                            </p>
                            <a x-show="imageSearchUrl && !imageUrl" x-cloak :href="imageSearchUrl" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded px-3 py-1.5 mt-2 transition-all cursor-pointer font-mono">
                                🔍 Cari Gambar di Google Images &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-neutral-400 mb-1.5 uppercase tracking-wider font-mono">Tags (Pisahkan dengan koma)</label>
                <input name="tags_input" x-model="tagsInput" type="text" class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-2 px-3 text-xs text-white placeholder:text-neutral-600 focus:outline-none" placeholder="snort, forensics, windows, registry">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2 font-mono">
                <label class="block text-xs font-bold text-neutral-400 uppercase tracking-widest">Isi Artikel (Mendukung Markdown) <span class="text-red-500">*</span></label>
                <button type="button" @click="showPreview = !showPreview" class="bg-neutral-950 hover:bg-neutral-800 text-[10px] text-neutral-400 hover:text-white py-1 px-3 rounded border border-neutral-800 cursor-pointer transition-colors" x-text="showPreview ? 'Kembali ke Kode' : 'Tampilan Pratinjau'"></button>
            </div>
            <textarea name="content" x-show="!showPreview" x-model="content" required rows="12" class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded-lg p-3.5 font-mono text-xs text-neutral-200 placeholder:text-neutral-750 focus:outline-none" placeholder="Tulis naskah lengkap menggunakan standar pemformatan Markdown..."></textarea>
            <div x-show="showPreview" x-cloak class="w-full min-h-[250px] bg-neutral-950 border border-neutral-800 rounded-lg p-4 font-sans text-xs text-neutral-300 overflow-y-auto whitespace-pre-wrap leading-relaxed"><h4 class="text-neutral-100 font-mono font-bold mb-3 border-b border-neutral-800 pb-2">PRATINJAU DRAF:</h4><span x-text="content || 'Belum ada konten untuk dipratinjau. Isi kotak teks untuk melihat tampilannya.'"></span></div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-800 font-mono">
            <a href="{{ route('admin.index') }}" class="bg-neutral-950 hover:bg-neutral-850 text-neutral-400 hover:text-white font-bold py-2.5 px-4 rounded text-xs uppercase tracking-wider transition-colors cursor-pointer">Batalkan</a>
            <button type="submit" class="bg-blue-650 hover:bg-blue-600 text-white font-black py-2.5 px-5 rounded text-xs uppercase tracking-wider transition-all shadow-md cursor-pointer">Terbitkan Berita</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function adminArticleForm() {
    return {
        title: @json(old('title', $article->title)),
        summary: @json(old('summary', $article->summary)),
        content: @json(old('content', $article->content)),
        category: @json(old('category', $article->category ?: 'Incident Response')),
        author: @json(old('author', $article->author)),
        imageUrl: @json(old('image_url', $article->image_url)),
        tagsInput: @json($initialTagsInput),
        activeAiProvider: @json($activeAiProvider),
        activeAiLabel: @json($activeAiLabel),
        aiKeyword: '',
        aiLoading: false,
        aiError: '',
        aiSuccess: '',
        imageSuggestion: '',
        imageSearchUrl: '',
        showPreview: false,
        async generateDraft() {
            if (!this.aiKeyword.trim()) { this.aiError = 'Mohon masukkan kata kunci topik terlebih dahulu.'; return; }
            if (this.activeAiProvider !== 'openrouter') {
                this.aiError = 'Provider AI tidak dikonfigurasi untuk mode browser. Hanya OpenRouter yang didukung.';
                return;
            }
            this.aiLoading = true; this.aiError = ''; this.aiSuccess = '';
            try {
                await this.generateDraftDirect();
            } catch (directError) {
                console.warn('Direct OpenRouter failed, trying backend fallback:', directError.message);
                try {
                    await this.generateDraftBackend();
                } catch (backendError) {
                    this.aiSuccess = '';
                    this.aiError = backendError.message || 'Gagal menghubungi AI. Silakan coba lagi.';
                }
            }
            finally { this.aiLoading = false; }
        },
        curatedImageUrl(query) {
            const q = (query || '').toLowerCase();
            const pools = {
                forensics: [
                    'https://images.unsplash.com/photo-1601597111158-2fceff270190?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1200&q=80'
                ],
                ransomware: [
                    'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1563206767-5b18f218e8de?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80'
                ],
                network: [
                    'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1518779578993-ec3579fee39f?auto=format&fit=crop&w=1200&q=80'
                ],
                linux: [
                    'https://images.unsplash.com/photo-1629654297299-c8506221ca97?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1629654291663-b91ad427698f?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1504639725590-34d0984388bd?auto=format&fit=crop&w=1200&q=80'
                ],
                default: [
                    'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80'
                ]
            };
            let pool = pools.default;
            if (q.includes('forensik') || q.includes('forensic')) pool = pools.forensics;
            else if (q.includes('ransomware') || q.includes('registry') || q.includes('malware') || q.includes('windows')) pool = pools.ransomware;
            else if (q.includes('ddos') || q.includes('cloudflare') || q.includes('network') || q.includes('traffic')) pool = pools.network;
            else if (q.includes('linux') || q.includes('ssh') || q.includes('terminal')) pool = pools.linux;
            const idx = Math.abs(hashCode(q)) % pool.length;
            return pool[idx];
            function hashCode(s) { let h = 0; for (let i = 0; i < s.length; i++) { h = ((h << 5) - h) + s.charCodeAt(i); h |= 0; } return h; }
        },
        async generateDraftDirect() {
            const apiKey = document.querySelector('meta[name="openrouter-key"]')?.content || '';
            const categories = (document.querySelector('meta[name="openrouter-categories"]')?.content || '').split(',');
            if (!apiKey) throw new Error('OpenRouter API Key tidak tersedia di browser.');

            const modelList = [
                'openai/gpt-oss-120b:free',
                'nvidia/nemotron-3-super:free',
                'google/gemma-4-31b-it:free',
                'nex-agi/nex-n2-pro:free'
            ];
            const prompt = 'Tulis sebuah draf artikel berita siber (cybersecurity bulletin) mendalam bertema pertahanan siber.\nTopik yang diinginkan: "' + this.aiKeyword + '"\nPilih kategori yang PALING SESUAI dengan topik artikel dari kategori yang tersedia. JANGAN selalu pilih kategori pertama atau kategori default. Pilih berdasarkan isi topik yang diminta. Kategori yang tersedia HANYA salah satu dari: ' + categories.join(', ') + '.\n\nAdvis siber harus mendalam, memotivasi pembaca (kalangan profesional SOC atau mahasiswa keamanan informasi), menerangkan rincian penanggulangan teknis, dan ditulis lengkap dalam bahasa Indonesia yang formal, berbobot, dan mudah dipahami. Gunakan pemformatan Markdown yang tepat dengan sub-judul, daftar poin, dsb.\n\nUntuk gambar: isi imageQuery dengan 3-5 kata kunci pencarian gambar yang sangat spesifik dan deskriptif dalam bahasa Inggris, misalnya "ddos attack network traffic monitoring server room dark".';
            const systemMsg = 'Anda adalah koresponden senior berita keamanan siber, redaktur jurnal pertahanan siber, dan ahli mitigasi siber. Tugas Anda adalah memproduksi buletin siber berkualitas jurnal tinggi tentang pertahanan siber menggunakan format JSON. Pastikan SEMUA field JSON terisi: title, summary, content, category, tags, imageQuery. Jangan ada yang kosong.';

            for (let i = 0; i < modelList.length; i++) {
                const model = modelList[i];
                console.log('Mencoba model [' + (i + 1) + '/' + modelList.length + ']:', model);
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 15000);
                    const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + apiKey, 'HTTP-Referer': window.location.origin, 'X-Title': 'Website Informasi' },
                        body: JSON.stringify({ model, messages: [{ role: 'system', content: systemMsg }, { role: 'user', content: prompt }], temperature: 0.7 }),
                        signal: controller.signal
                    });
                    clearTimeout(timeoutId);
                    if (response.status === 429) { console.warn('Model', model, 'rate limited (429), skip ke model berikutnya...'); continue; }
                    if (!response.ok) { const e = await response.json().catch(() => ({})); console.warn('Model', model, 'error', response.status, e?.error?.message); continue; }
                    const result = await response.json();
                    const raw = (result?.choices?.[0]?.message?.content || '').trim();
                    let text = raw.replace(/^```(?:json)?\s*/i, '').replace(/\s*```$/i, '').trim();
                    let decoded = null;
                    try { decoded = JSON.parse(text); } catch (_) {}
                    if (!decoded && text.includes('{')) { const m = text.match(/\{[\s\S]*\}/); if (m) try { decoded = JSON.parse(m[0]); } catch (_) {} }
                    if (!decoded || typeof decoded !== 'object') { console.warn('Model', model, 'bukan JSON valid, skip...'); continue; }
                    const draft = {
                        title: decoded.title || decoded.judul || decoded.nama || 'Artikel Keamanan Siber',
                        summary: decoded.summary || decoded.ringkasan || decoded.deskripsi || '',
                        content: decoded.content || decoded.isi || decoded.konten || decoded.body || decoded.article || '',
                        category: decoded.category || decoded.kategori || this.category || '',
                        tags: Array.isArray(decoded.tags || decoded.tag || decoded.labels) ? (decoded.tags || decoded.tag || decoded.labels) : [],
                        imageQuery: decoded.imageQuery || decoded.image_query || decoded.gambar_query || '',
                        imageUrl: '',
                        imageSearchUrl: ''
                    };
                    if (!draft.summary || !draft.content || !draft.tags.length) { console.warn('Model', model, 'response tidak lengkap, skip...'); continue; }
                    if (draft.category && categories.some(c => c.toLowerCase() === draft.category.toLowerCase())) { draft.category = categories.find(c => c.toLowerCase() === draft.category.toLowerCase()); }
                    if (!draft.imageQuery) draft.imageQuery = this.aiKeyword.toLowerCase().replace(/[^a-z0-9\s]+/g, ' ').trim() + ' cybersecurity security dark editorial';
                    draft.imageUrl = this.curatedImageUrl(draft.imageQuery);
                    draft.imageSearchUrl = 'https://www.google.com/search?tbm=isch&q=' + encodeURIComponent(draft.imageQuery);
                    console.log('Berhasil dengan model:', model);
                    this.fillDraft(draft);
                    this.aiSuccess = 'Draf berita siber berhasil diproduksi memakai model ' + model + ' dari OpenRouter langsung dari browser!';
                    return;
                } catch (e) {
                    if (e.name === 'AbortError') { console.warn('Model', model, 'timeout (15 detik), skip ke model berikutnya...'); continue; }
                    console.warn('Model', model, 'error:', e.message);
                    continue;
                }
            }
            throw new Error('Semua model OpenRouter gagal. Silakan coba lagi atau gunakan mode backend.');
        },
        async generateDraftBackend() {
            const response = await fetch('{{ route('admin.gemini.generate-draft') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ promptKeyword: this.aiKeyword, category: this.category })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Backend gagal menggenerate artikel draf.');
            this.fillDraft(data);
            if (data.fallback) {
                this.aiSuccess = data.error || 'Mode cadangan aktif dari server.';
            } else {
                this.aiSuccess = 'Draf berita siber berhasil diproduksi dari server!';
            }
        },
        fillDraft(data) {
            this.title = data.title || '';
            this.summary = data.summary || '';
            this.content = data.content || '';
            this.tagsInput = Array.isArray(data.tags) ? data.tags.join(', ') : '';
            this.imageUrl = data.imageUrl || '';
            this.imageSuggestion = data.imageQuery || '';
            this.imageSearchUrl = data.imageSearchUrl || '';
            const categories = (document.querySelector('meta[name="openrouter-categories"]')?.content || '').split(',');
            const incomingCategory = (data.category || '').trim();
            if (incomingCategory) {
                const match = categories.find(c => c.toLowerCase() === incomingCategory.toLowerCase());
                const fuzzy = categories.find(c => c.toLowerCase().includes(incomingCategory.toLowerCase()) || incomingCategory.toLowerCase().includes(c.toLowerCase()));
                const resolved = match || fuzzy || null;
                if (resolved) {
                    setTimeout(() => { this.category = resolved; }, 50);
                }
            }
        }
    };
}
</script>
@endpush
@endsection
