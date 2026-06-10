@extends('layouts.app')

@section('title', $article->title)

@section('content')
@php($cameFromAdmin = request('from') === 'admin' || (session('admin_logged_in') && !request()->has('from')))
@php($backUrl = $cameFromAdmin ? route('admin.index') : route('home'))
<div class="max-w-3xl mx-auto bg-neutral-900 border border-neutral-800 rounded-2xl shadow-2xl p-5 sm:p-8 overflow-hidden">
    <a href="{{ $backUrl }}" class="inline-block mb-5 text-xs font-mono uppercase tracking-wider text-blue-400 hover:text-blue-300">&larr; {{ $cameFromAdmin ? 'Kembali ke Konsol CMS' : 'Kembali ke Portal' }}</a>
    @include('articles._detail', ['article' => $article])
</div>
@endsection
