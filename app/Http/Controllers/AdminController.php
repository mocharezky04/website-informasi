<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::query()->latest('published_date');

        if ($request->filled('q')) {
            $search = $request->string('q')->lower()->toString();
            $query->where(function ($builder) use ($search) {
                $builder->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(summary) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(author) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(category) LIKE ?', ["%{$search}%"]);
            });
        }

        return view('admin.index', [
            'articles' => $query->get(),
            'search' => $request->get('q', ''),
        ]);
    }

    public function create()
    {
        return view('admin.form', [
            'article' => new Article([
                'category' => 'Incident Response',
                'author' => 'SOC Team Analyst',
                'image_url' => '',
                'tags' => ['incident-response', 'security', 'blue-team'],
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedArticleData($request);
        $data['published_date'] = now()->toDateString();
        $data['image_url'] = $this->resolveImageUrl($request, $data['image_url'] ?? null);

        Article::create($data);

        return redirect()->route('admin.index')->with('success', 'Draf artikel baru berhasil diterbitkan!');
    }

    public function edit(Article $article)
    {
        return view('admin.form', [
            'article' => $article,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $data = $this->validatedArticleData($request);
        $data['image_url'] = $this->resolveImageUrl($request, $data['image_url'] ?? $article->image_url);

        $article->update($data);

        return redirect()->route('admin.index')->with('success', 'Advis keamanan berhasil diperbarui!');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return redirect()->route('admin.index')->with('success', 'Artikel berhasil dihapus.');
    }

    private function validatedArticleData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'content' => ['required', 'string'],
            'category' => ['required', 'string', 'max:80'],
            'author' => ['nullable', 'string', 'max:120'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'tags_input' => ['nullable', 'string'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $data['author'] = $data['author'] ?: 'SOC Team Contributor';
        $data['tags'] = collect(explode(',', $data['tags_input'] ?? ''))
            ->map(fn ($tag) => Str::of($tag)->trim()->lower()->toString())
            ->filter()
            ->values()
            ->all();

        unset($data['tags_input'], $data['image_file']);

        return $data;
    }

    private function resolveImageUrl(Request $request, ?string $currentUrl): string
    {
        if (!$request->hasFile('image_file')) {
            return $currentUrl ?: '';
        }

        $uploadDir = public_path('uploads');
        if (!File::exists($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true);
        }

        $file = $request->file('image_file');
        $name = 'upload-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($uploadDir, $name);

        return '/uploads/'.$name;
    }
}
