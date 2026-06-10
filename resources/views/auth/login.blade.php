@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
<div class="fixed inset-0 bg-neutral-950/85 backdrop-blur-sm flex items-center justify-center p-4 z-40">
    <div class="w-full max-w-md bg-neutral-900 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-2xl relative">
        <a href="{{ route('home') }}" class="absolute top-4 right-4 text-neutral-400 hover:text-white transition-colors cursor-pointer">X</a>
        <div class="flex flex-col items-center mb-6">
            <div class="w-12 h-12 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-450 mb-3 font-mono">LOCK</div>
            <h2 class="text-xl font-serif italic text-white text-center">Pusat Otentikasi</h2>
            <p class="text-[11px] text-neutral-500 text-center uppercase tracking-wider mt-1.5 font-mono">Command Authorization Hub</p>
        </div>
        <div class="bg-neutral-950 border border-neutral-800 rounded-lg p-3.5 mb-5 font-mono text-xs">
            <p class="text-[11px] font-bold text-blue-400 uppercase tracking-wider">UAS Kredensial Admin Penguji</p>
            <div class="grid grid-cols-2 gap-2 mt-2 text-[10.5px] text-neutral-400">
                <div>Username: <span class="text-neutral-150 font-bold font-sans">admin</span></div>
                <div>Password: <span class="text-neutral-150 font-bold font-sans">blueteam2026</span></div>
            </div>
        </div>
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded-lg p-3 mb-4 font-mono">{{ session('error') }}</div>
        @endif
        <form method="POST" action="{{ route('login.store') }}" class="space-y-4 font-mono text-[11px]">
            @csrf
            <div>
                <label class="block font-bold text-neutral-400 mb-1.5 uppercase tracking-widest">Username</label>
                <input name="username" type="text" required class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-2 px-3 text-xs text-white placeholder:text-neutral-700 font-sans focus:outline-none transition-colors" placeholder="Masukkan username admin..." value="{{ old('username') }}">
            </div>
            <div x-data="{ show: false }">
                <label class="block font-bold text-neutral-400 mb-1.5 uppercase tracking-widest">Password</label>
                <div class="relative">
                    <input name="password" :type="show ? 'text' : 'password'" required class="w-full bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded py-2 pl-3 pr-20 text-xs text-white placeholder:text-neutral-700 font-sans focus:outline-none transition-colors" placeholder="Masukkan password admin...">
                    <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-550 hover:text-white transition-colors cursor-pointer" x-text="show ? 'Hide' : 'Show'"></button>
                </div>
            </div>
            <button type="submit" class="w-full bg-blue-650 hover:bg-blue-650/90 text-white font-black py-2.5 rounded text-xs uppercase tracking-wider transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/40 cursor-pointer mt-4">Masuk ke Konsol Admin</button>
        </form>
    </div>
</div>
@endsection
