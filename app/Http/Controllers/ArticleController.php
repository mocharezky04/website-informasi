<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public const CATEGORIES = [
        'Incident Response',
        'Threat Intelligence',
        'System Hardening',
        'Digital Forensics',
        'Vulnerability Management',
    ];

    public function index(Request $request)
    {
        $query = Article::query()->latest('published_date');

        if ($request->filled('q')) {
            $search = $request->string('q')->lower()->toString();
            $query->where(function ($builder) use ($search) {
                $builder->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(summary) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(content) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(author) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(tags) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        return view('home', [
            'articles' => $query->get(),
            'categories' => self::CATEGORIES,
            'currentSearch' => $request->get('q', ''),
            'selectedCategory' => $request->get('category', 'All'),
        ]);
    }

    public function show(Article $article)
    {
        return view('articles.show', [
            'article' => $article,
            'categories' => self::CATEGORIES,
        ]);
    }
}
