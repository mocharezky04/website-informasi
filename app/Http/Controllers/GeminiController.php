<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeminiController extends Controller
{
    public function analyzeLog(Request $request)
    {
        $data = $request->validate([
            'logText' => ['required', 'string'],
            'contextType' => ['nullable', 'string'],
        ]);

        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            return response()->json([
                'analysis' => '[FALLBACK] Gemini API Key belum dikonfigurasi di backend. Menjalankan analisis statis lokal: Log menunjukkan indikasi berbahaya. Silakan atur kunci API Anda di .env untuk analisis mendalam AI.',
                'threatLevel' => 'High',
                'classification' => 'Aktivitas Mencurigakan (Statik)',
                'indicators' => ['Kunci API Tidak Ditemukan'],
                'remediationSteps' => [
                    'Konfigurasikan GEMINI_API_KEY di lingkungan Laravel.',
                    'Lihat detail berkas log secara mendalam manual.',
                ],
            ]);
        }

        $prompt = 'Lakukan analisis mendalam terhadap log keamanan/telemetri serangan siber berikut ini. Jenis sistem atau konteks log: '.($data['contextType'] ?? 'Umum').".
Log Konten:
```
{$data['logText']}
```
Identifikasi taktik serangan, klasifikasikan serangan, berikan tingkat ancaman (Low/Medium/High/Critical), sebutkan indikasi kompromisasi (IoC), serta jelaskan rekomendasi praktis mitigasi bagi seorang analis keamanan.";

        try {
            return response()->json($this->callGeminiJson($prompt, 'Anda adalah analis senior keamanan siber dan SOC (Security Operations Center) Investigator utama. Tugas Anda adalah membaca berkas log, membedah serangan siber, dan menjelaskan ancaman tersebut dalam bahasa Indonesia dengan istilah keamanan teknis yang akurat. Berikan analisis terperinci dalam format JSON.', $apiKey, [
                'analysis' => 'string',
                'threatLevel' => 'string',
                'classification' => 'string',
                'indicators' => 'array',
                'remediationSteps' => 'array',
            ]));
        } catch (\Throwable $error) {
            return response()->json($this->fallbackAnalysis($data, $error));
        }
    }

    public function generateDraft(Request $request)
    {
        $data = $request->validate([
            'promptKeyword' => ['required', 'string'],
            'category' => ['nullable', 'string'],
        ]);

        $provider = strtolower((string) config('services.ai.provider', 'gemini'));
        $apiKey = config('services.gemini.api_key');

        if ($provider === 'openrouter') {
            $openRouterKey = config('services.openrouter.api_key');
            Log::info('AI draft request', [
                'provider' => 'openrouter',
                'model' => config('services.openrouter.model', 'openrouter/auto'),
                'keyword' => $data['promptKeyword'],
            ]);

            if (!$openRouterKey) {
                return response()->json($this->fallbackDraft($data, new \RuntimeException('OPENROUTER_API_KEY belum dikonfigurasi.')));
            }

            try {
                return response()->json($this->callOpenRouterDraft($data, $openRouterKey));
            } catch (\Throwable $error) {
                $code = (int) $error->getCode();
                Log::error('OpenRouter draft failed', [
                    'message' => $error->getMessage(),
                    'code' => $code,
                ]);

                // 429 rate limit — tampilkan pesan ke user, JANGAN fallback ke lokal
                if ($code === 429) {
                    return response()->json([
                        'error' => 'OpenRouter sedang rate limit (terlalu banyak request). Silakan tunggu beberapa saat lalu coba lagi.',
                        'fallback' => false,
                    ], 429);
                }

                // 401 auth error — tampilkan pesan ke user, JANGAN fallback ke lokal
                if ($code === 401) {
                    return response()->json([
                        'error' => 'API key OpenRouter tidak valid atau belum diizinkan. Silakan periksa OPENROUTER_API_KEY di .env.',
                        'fallback' => false,
                    ], 401);
                }

                // Network error / 5xx — fallback ke lokal
                return response()->json($this->fallbackDraft($data, $error));
            }
        }

        if (!$apiKey) {
            return response()->json([
                'error' => 'Kunci GEMINI_API_KEY belum dikonfigurasi di .env. Silakan tambahkan kunci API terlebih dahulu untuk memanfaatkan asisten penulisan artikel AI bertenaga tinggi!',
            ], 400);
        }

        $prompt = 'Tulis sebuah draf artikel berita siber (cybersecurity bulletin) mendalam bertema pertahanan siber.
Topik yang diinginkan: "'.$data['promptKeyword'].'"
Kategori target artikel: "'.($data['category'] ?? '').'"
Kategori yang tersedia HANYA salah satu dari: '.implode(', ', ArticleController::CATEGORIES).'.

Advis siber harus mendalam, memotivasi pembaca (kalangan profesional SOC atau mahasiswa keamanan informasi), menerangkan rincian penanggulangan teknis, dan ditulis lengkap dalam bahasa Indonesia yang formal, berbobot, dan mudah dipahami. Gunakan pemformatan Markdown yang tepat dengan sub-judul, daftar poin, dsb.

Untuk gambar: isi field imageUrl dengan URL gambar NYATA dari web manapun (Unsplash, Pixabay, Pexels, Wikimedia, atau sumber gambar bebas lainnya) yang benar-benar valid dan bisa diakses. JANGAN mengisi imageUrl jika tidak yakin 100% URL-nya valid — sebagai gantinya isi imageQuery dengan 3-5 kata kunci pencarian gambar yang sangat spesifik dan deskriptif dalam bahasa Inggris. JANGAN pakai example.com atau URL palsu.';

        try {
            $draft = $this->callGeminiJson($prompt, 'Anda adalah koresponden senior berita keamanan siber, redaktur jurnal pertahanan siber, dan ahli mitigasi siber. Tugas Anda adalah memproduksi buletin siber berkualitas jurnal tinggi tentang pertahanan siber menggunakan format JSON.', $apiKey, [
                'title' => 'string',
                'summary' => 'string',
                'content' => 'string',
                'category' => 'string',
                'tags' => 'array',
                'imageUrl' => 'string',
                'imageQuery' => 'string',
            ]);

            $draft['imageQuery'] = trim((string) ($draft['imageQuery'] ?? $this->imageQueryForTopic((string) $data['promptKeyword'])));
            $draft['category'] = $this->normalizeCategory($draft['category'] ?? '', $data['category'] ?? '');
            $draft['imageUrl'] = $this->validImageUrl($draft['imageUrl'] ?? '');
            $draft['imageSearchUrl'] = $draft['imageUrl'] ? '' : $this->buildImageSearchUrl($draft['imageQuery']);

            return response()->json($draft);
        } catch (\Throwable $error) {
            return response()->json($this->fallbackDraft($data, $error));
        }
    }

    private function callOpenRouterDraft(array $data, string $apiKey): array
    {
        $schemaPrompt = 'Balas hanya JSON valid tanpa markdown fence dengan format: {"title":"string","summary":"string","content":"string markdown bahasa Indonesia lengkap","category":"string","tags":["string"],"imageUrl":"string","imageQuery":"string"}.';
        $prompt = 'Tulis sebuah draf artikel berita siber (cybersecurity bulletin) mendalam bertema pertahanan siber.
Topik yang diinginkan: "'.$data['promptKeyword'].'"
Kategori target artikel: "'.($data['category'] ?? '').'"
Kategori yang tersedia HANYA salah satu dari: '.implode(', ', ArticleController::CATEGORIES).'.

Advis siber harus mendalam, memotivasi pembaca (kalangan profesional SOC atau mahasiswa keamanan informasi), menerangkan rincian penanggulangan teknis, dan ditulis lengkap dalam bahasa Indonesia yang formal, berbobot, dan mudah dipahami. Gunakan pemformatan Markdown yang tepat dengan sub-judul, daftar poin, dsb.

Untuk gambar: isi field imageUrl dengan URL gambar NYATA dari web manapun (Unsplash, Pixabay, Pexels, Wikimedia, atau sumber gambar bebas lainnya) yang benar-benar valid dan bisa diakses. JANGAN mengisi imageUrl jika tidak yakin 100% URL-nya valid — sebagai gantinya isi imageQuery dengan 3-5 kata kunci pencarian gambar yang sangat spesifik dan deskriptif dalam bahasa Inggris. JANGAN pakai example.com atau URL palsu.';

        $model = config('services.openrouter.model', 'openrouter/auto');
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Anda adalah koresponden senior berita keamanan siber, redaktur jurnal pertahanan siber, dan ahli mitigasi siber. Tugas Anda adalah memproduksi buletin siber berkualitas jurnal tinggi tentang pertahanan siber menggunakan format JSON.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
        ];

        // Retry logic untuk 429 rate limit
        $response = null;
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'HTTP-Referer' => config('services.openrouter.site_url'),
                'X-Title' => config('services.openrouter.app_name'),
            ])->connectTimeout(15)->timeout(90)->post('https://openrouter.ai/api/v1/chat/completions', $payload);

            if (!in_array($response->status(), [429], true) || $attempt === 5) {
                break;
            }

            usleep(min(8000, 2000 * $attempt) * 1000);
        }

        if (!$response->successful()) {
            $status = $response->status();
            $msg = $response->json('error.message') ?: 'OpenRouter gagal membuat draf.';

            // 429 rate limit — jangan fallback ke lokal
            if ($status === 429) {
                throw new \RuntimeException('OpenRouter sedang rate limit (terlalu banyak request). Silakan tunggu beberapa saat lalu coba lagi.', 429);
            }

            // 401 auth error — jangan fallback ke lokal
            if ($status === 401) {
                throw new \RuntimeException('API key OpenRouter tidak valid atau belum diizinkan. Silakan periksa OPENROUTER_API_KEY di .env.', 401);
            }

            // Network error / 5xx / error lainnya — boleh fallback ke lokal
            throw new \RuntimeException($msg, $status);
        }

        $raw = trim((string) $response->json('choices.0.message.content', '{}'));
        Log::info('OpenRouter raw response', ['raw' => mb_substr($raw, 0, 2000)]);

        // Bersihkan markdown fence jika ada
        $text = preg_replace('/^```(?:json)?\s*/i', '', $raw);
        $text = preg_replace('/\s*```$/i', '', $text);
        $text = trim($text);

        // Coba decode JSON langsung
        $decoded = json_decode($text, true);

        // Jika gagal, coba cari JSON dalam teks (kadang AI bungkus JSON dalam teks)
        if (!is_array($decoded)) {
            if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
                $decoded = json_decode($matches[0], true);
            }
        }

        if (!is_array($decoded)) {
            Log::error('OpenRouter JSON parse failed', ['raw' => mb_substr($raw, 0, 2000)]);
            throw new \RuntimeException('Respons OpenRouter tidak dapat dibaca sebagai JSON: '.mb_substr($raw, 0, 200));
        }

        Log::info('OpenRouter parsed keys', ['keys' => array_keys($decoded)]);

        // Fleksibel: handle both English keys (title, summary, content) AND Indonesian keys (judul, ringkasan, isi)
        $draft = [
            'title' => $decoded['title'] ?? $decoded['judul'] ?? $decoded['nama'] ?? 'Artikel Keamanan Siber',
            'summary' => $decoded['summary'] ?? $decoded['ringkasan'] ?? $decoded['deskripsi'] ?? '',
            'content' => $decoded['content'] ?? $decoded['isi'] ?? $decoded['konten'] ?? $decoded['body'] ?? $decoded['article'] ?? '',
            'category' => $this->normalizeCategory($decoded['category'] ?? $decoded['kategori'] ?? '', $data['category'] ?? ''),
            'tags' => is_array($decoded['tags'] ?? $decoded['tag'] ?? $decoded['labels'] ?? null) ? ($decoded['tags'] ?? $decoded['tag'] ?? $decoded['labels']) : [],
            'imageQuery' => trim((string) ($decoded['imageQuery'] ?? $decoded['image_query'] ?? $decoded['gambar_query'] ?? $this->imageQueryForTopic((string) $data['promptKeyword']))),
            'imageUrl' => $decoded['imageUrl'] ?? $decoded['image_url'] ?? $decoded['gambar'] ?? $decoded['url_gambar'] ?? '',
            'provider' => 'openrouter',
        ];

        Log::info('OpenRouter draft mapped', [
            'title' => mb_substr($draft['title'], 0, 100),
            'content_len' => mb_strlen($draft['content']),
            'category' => $draft['category'],
            'tags' => $draft['tags'],
        ]);

        // Validasi field penting — jika kosong, fallback ke lokal
        if (empty($draft['summary']) || empty($draft['content']) || empty($draft['tags'])) {
            throw new \RuntimeException('Response OpenRouter tidak lengkap (summary, content, atau tags kosong).');
        }

        $draft['imageUrl'] = $this->validImageUrl($draft['imageUrl'] ?? '');
        $draft['imageSearchUrl'] = $draft['imageUrl'] ? '' : $this->buildImageSearchUrl($draft['imageQuery']);

        return $draft;
    }

    private function validImageUrl(?string $url): string
    {
        $url = trim((string) $url);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }

        if (str_contains(strtolower($url), 'example.com')) {
            return '';
        }

        return $url;
    }

    private function imageQueryForTopic(string $topic): string
    {
        $topic = Str::of($topic)
            ->lower()
            ->replaceMatches('/[^a-z0-9\s-]+/', ' ')
            ->squish()
            ->toString();

        return trim($topic.' cybersecurity defensive security dark editorial');
    }

    private function curatedImageUrl(string $query): string
    {
        $query = strtolower($query);
        $pools = [
            'forensics' => [
                'https://images.unsplash.com/photo-1601597111158-2fceff270190?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?auto=format&fit=crop&w=1200&q=80',
            ],
            'ransomware' => [
                'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1563206767-5b18f218e8de?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80',
            ],
            'network' => [
                'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1518779578993-ec3579fee39f?auto=format&fit=crop&w=1200&q=80',
            ],
            'linux' => [
                'https://images.unsplash.com/photo-1629654297299-c8506221ca97?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1629654291663-b91ad427698f?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1504639725590-34d0984388bd?auto=format&fit=crop&w=1200&q=80',
            ],
            'default' => [
                'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        $pool = $pools['default'];
        if (str_contains($query, 'forensik') || str_contains($query, 'forensic')) {
            $pool = $pools['forensics'];
        } elseif (str_contains($query, 'ransomware') || str_contains($query, 'registry') || str_contains($query, 'malware') || str_contains($query, 'windows')) {
            $pool = $pools['ransomware'];
        } elseif (str_contains($query, 'ddos') || str_contains($query, 'cloudflare') || str_contains($query, 'network') || str_contains($query, 'traffic')) {
            $pool = $pools['network'];
        } elseif (str_contains($query, 'linux') || str_contains($query, 'ssh') || str_contains($query, 'terminal')) {
            $pool = $pools['linux'];
        }

        return $pool[abs(crc32($query)) % count($pool)];
    }

    private function callGeminiJson(string $prompt, string $systemInstruction, string $apiKey, array $fields): array
    {
        $properties = collect($fields)->mapWithKeys(function ($type, $name) {
            if ($type === 'array') {
                return [$name => ['type' => 'ARRAY', 'items' => ['type' => 'STRING']]];
            }

            return [$name => ['type' => 'STRING']];
        })->all();

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema' => [
                    'type' => 'OBJECT',
                    'properties' => $properties,
                    'required' => array_keys($fields),
                ],
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ],
        ];

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
            config('services.gemini.model', 'gemini-2.0-flash'),
            $apiKey
        );

        $response = null;
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = Http::connectTimeout(10)->timeout(25)->post($url, $payload);

            if (!in_array($response->status(), [429, 503], true) || $attempt === 5) {
                break;
            }

            usleep(min(6000, 1500 * $attempt) * 1000);
        }

        if (!$response->successful()) {
            throw new \RuntimeException($response->json('error.message') ?: 'Terjadi kendala teknis pada layanan Gemini.', $response->status());
        }

        $text = $response->json('candidates.0.content.parts.0.text', '{}');
        $decoded = json_decode($text, true);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Respons Gemini tidak dapat dibaca sebagai JSON.');
        }

        return $decoded;
    }

    private function formatGeminiError(\Throwable $error): string
    {
        $message = strtolower($error->getMessage());
        $code = (int) $error->getCode();

        if (str_contains($message, 'overloaded') || str_contains($message, 'high demand') || str_contains($message, 'temporary') || str_contains($message, 'unavailable') || $code === 503) {
            return 'Server AI sedang kelebihan beban (Error 553: Google Gemini High Demand). Spikes trafik bersifat sementara. Silakan tunggu 5-10 detik lalu coba klik ulang tombol kembali.';
        }

        if (str_contains($message, 'rate limit') || str_contains($message, 'quota') || $code === 429) {
            return 'Kuota Gemini untuk API key/proyek ini sedang penuh. Sistem memakai mode cadangan lokal agar penulisan artikel tetap bisa berjalan.';
        }

        if (str_contains($message, 'curl error 28') || str_contains($message, 'operation timed out') || str_contains($message, 'timed out')) {
            return 'Permintaan ke Gemini terlalu lama dan habis waktu. Di hosting bersama, ini bisa terjadi karena koneksi keluar lambat atau dibatasi. Coba lagi beberapa saat, atau gunakan model AI secara lokal dengan fallback sementara.';
        }

        if (str_contains($message, 'api key') || str_contains($message, 'invalid key') || str_contains($message, 'auth')) {
            return 'Kesalahan Autentikasi: API Key Gemini tidak valid atau belum terkonfigurasi dengan benar di .env.';
        }

        return $error->getMessage() ?: 'Terjadi kendala teknis pada layanan kecerdasan buatan Gemini.';
    }

    private function fallbackAnalysis(array $data, \Throwable $error): array
    {
        $context = trim((string) ($data['contextType'] ?? 'Umum'));
        $snippet = trim(preg_replace('/\s+/', ' ', (string) $data['logText']));
        $snippet = mb_substr($snippet, 0, 120);

        return [
            'analysis' => '[FALLBACK] Gemini tidak merespons tepat waktu. Secara lokal, log ini perlu ditinjau manual karena memuat pola yang berpotensi mencurigakan. Cuplikan: '.$snippet,
            'threatLevel' => 'High',
            'classification' => 'Aktivitas Mencurigakan (Fallback)',
            'indicators' => [
                'Analisis AI gagal dijalankan',
                'Konteks log: '.$context,
            ],
            'remediationSteps' => [
                'Tinjau log secara manual untuk mencari lonjakan autentikasi atau aktivitas proses yang tidak biasa.',
                'Jalankan analisis lanjutan saat layanan AI kembali stabil.',
                'Pastikan endpoint dan akun terkait sudah diperiksa secara forensik.',
            ],
            'error' => $this->formatGeminiError($error),
            'fallback' => true,
        ];
    }

    private function fallbackDraft(array $data, \Throwable $error): array
    {
        $keyword = trim((string) $data['promptKeyword']);
        $category = trim((string) ($data['category'] ?? 'Vulnerability Management'));
        $cleanKeyword = Str::of($keyword)
            ->replaceMatches('/\banalisis\b/i', '')
            ->replaceMatches('/\bdigital\b/i', 'digital')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
        $displayKeyword = $cleanKeyword !== '' ? $cleanKeyword : $keyword;
        $slug = Str::of($displayKeyword)->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish();
        $fallbackTitle = $slug->isEmpty()
            ? 'Analisis Keamanan Siber'
            : 'Analisis '.Str::title($slug->toString());

        $summary = 'Panduan ringkas tentang '.$displayKeyword.' dari sudut pandang pertahanan siber, berisi konteks risiko, indikator pemantauan, dan langkah mitigasi praktis.';

        $content = implode("\n\n", [
            '### Pendahuluan',
            'Topik **'.$displayKeyword.'** penting dipahami karena sering berkaitan dengan deteksi dini, penguatan kontrol keamanan, dan respons cepat saat terjadi insiden. Tim keamanan perlu melihat isu ini dari sisi indikator teknis, dampak operasional, serta tindakan mitigasi yang dapat dijalankan.',
            '### Fokus Pemantauan',
            '1. Identifikasi aset, akun, atau layanan yang paling mungkin terdampak.',
            '2. Pantau log autentikasi, perubahan konfigurasi, proses mencurigakan, dan koneksi jaringan yang tidak biasa.',
            '3. Catat indikator kompromi seperti alamat IP asing, nama proses tidak dikenal, perubahan registry, atau percobaan akses berulang.',
            '### Langkah Mitigasi',
            '- Terapkan prinsip least privilege pada akun pengguna dan akun layanan.',
            '- Aktifkan logging yang memadai agar aktivitas penting dapat ditelusuri kembali.',
            '- Perbarui sistem, aplikasi, dan konfigurasi keamanan secara berkala.',
            '- Siapkan prosedur isolasi host, reset kredensial, dan eskalasi insiden untuk kondisi darurat.',
            '### Kesimpulan',
            'Pendekatan defensif yang konsisten membantu organisasi mengurangi risiko sebelum berkembang menjadi insiden besar. Artikel ini dapat dikembangkan lagi dengan contoh log, studi kasus, atau perintah teknis sesuai kebutuhan.',
        ]);

        $imageQuery = $this->imageQueryForTopic($displayKeyword);
        $imageUrl = $this->curatedImageUrl($imageQuery);

        return [
            'title' => $fallbackTitle,
            'summary' => $summary,
            'content' => $content,
            'category' => $category ?: 'Vulnerability Management',
            'tags' => collect([
                Str::of($displayKeyword)->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString(),
                Str::of($category)->lower()->replaceMatches('/[^a-z0-9]+/', '-')->trim('-')->toString(),
                'defensive-security',
            ])->filter()->values()->all(),
            'imageQuery' => $imageQuery,
            'imageUrl' => $imageUrl,
            'imageSearchUrl' => $imageUrl ? '' : $this->buildImageSearchUrl($imageQuery),
            'error' => $this->formatProviderError($error),
            'fallback' => true,
        ];
    }

    private function normalizeCategory(string $aiCategory, string $fallbackCategory): string
    {
        $aiCategory = trim($aiCategory);
        if ($aiCategory === '') {
            return $fallbackCategory;
        }

        $categories = ArticleController::CATEGORIES;

        // Exact match
        foreach ($categories as $cat) {
            if (strcasecmp($aiCategory, $cat) === 0) {
                return $cat;
            }
        }

        // Fuzzy: contains match
        $lower = strtolower($aiCategory);
        foreach ($categories as $cat) {
            if (str_contains(strtolower($cat), $lower) || str_contains($lower, strtolower($cat))) {
                return $cat;
            }
        }

        // Keyword heuristic mapping
        $keywordMap = [
            'incident' => 'Incident Response',
            'response' => 'Incident Response',
            'insiden' => 'Incident Response',
            'threat' => 'Threat Intelligence',
            'intelligence' => 'Threat Intelligence',
            'ancaman' => 'Threat Intelligence',
            'hardening' => 'System Hardening',
            'pengerasan' => 'System Hardening',
            'sistem' => 'System Hardening',
            'forensic' => 'Digital Forensics',
            'forensik' => 'Digital Forensics',
            'digital' => 'Digital Forensics',
            'vulnerability' => 'Vulnerability Management',
            'vulnerabilit' => 'Vulnerability Management',
            'patch' => 'Vulnerability Management',
            'kerentanan' => 'Vulnerability Management',
        ];

        foreach ($keywordMap as $keyword => $mappedCategory) {
            if (str_contains($lower, $keyword)) {
                return $mappedCategory;
            }
        }

        return $fallbackCategory;
    }

    private function buildImageSearchUrl(string $query): string
    {
        $query = trim($query);
        if ($query === '') {
            return '';
        }

        return 'https://www.google.com/search?tbm=isch&q='.urlencode($query);
    }

    private function formatProviderError(\Throwable $error): string
    {
        $message = strtolower($error->getMessage());
        $code = (int) $error->getCode();

        if (str_contains($message, 'timed out') || str_contains($message, 'curl error 28') || $code === 28) {
            return 'Request ke OpenRouter habis waktu karena koneksi lambat. Sistem memakai mode cadangan lokal.';
        }

        if ($code >= 500) {
            return 'OpenRouter mengalami gangguan server ('.$code.'). Sistem memakai mode cadangan lokal.';
        }

        if (str_contains($message, 'json') || str_contains($message, 'parse') || str_contains($message, 'decode')) {
            return 'Respons dari OpenRouter tidak valid (bukan JSON). Model mungkin belum mendukung JSON structured output. Sistem memakai mode cadangan lokal.';
        }

        return 'OpenRouter gagal menjawab: '.$error->getMessage().'. Sistem memakai mode cadangan lokal.';
    }
}