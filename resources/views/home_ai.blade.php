<div id="log-analyst-panel" class="bg-neutral-900/40 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-xl relative overflow-hidden" x-data="logAnalyst()">
    <div class="absolute top-0 right-0 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl -z-10"></div>
    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-neutral-850 pb-5 mb-6 gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 font-mono">AI</div>
            <div>
                <h3 class="text-lg font-serif italic text-white flex flex-wrap items-center gap-2">Investigator Log Keamanan AI <span class="text-[9px] font-mono bg-neutral-950 text-blue-400 py-0.5 px-2.5 rounded-full border border-neutral-800 font-bold uppercase tracking-wider block">Gemini-3.5 Real-time</span></h3>
                <p class="text-xs text-neutral-450 mt-1 font-serif">Uji kerentanan insiden siber secara instans & temukan anomali taktik penyerang.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-5 flex flex-col space-y-4">
            <div>
                <span class="block text-[10px] font-mono font-bold text-neutral-400 uppercase tracking-widest mb-3">Pilih Contoh Insiden Log:</span>
                <div class="grid grid-cols-1 gap-2.5">
                    <template x-for="(sample, index) in samples" :key="index">
                        <button type="button" @click="logInput = sample.log; error = ''" class="group flex flex-col p-3 bg-neutral-950/60 hover:bg-neutral-950 border border-neutral-850 hover:border-neutral-700 rounded transition-all text-left relative overflow-hidden cursor-pointer">
                            <div class="flex items-center justify-between mb-1.5 gap-2">
                                <span class="text-xs font-serif font-bold text-neutral-150 group-hover:text-blue-400 transition-colors" x-text="sample.name"></span>
                                <span class="text-[9px] text-neutral-500 font-mono uppercase tracking-wider" x-text="sample.type"></span>
                            </div>
                            <p class="text-[10px] text-neutral-500 font-mono truncate w-full" x-text="sample.log"></p>
                        </button>
                    </template>
                </div>
            </div>

            <div class="flex-1 flex flex-col">
                <label class="block text-[10px] font-mono font-bold text-neutral-400 uppercase tracking-widest mb-2">Masukan Konsol Log (Kustom):</label>
                <div class="relative flex-1 min-h-[180px] lg:min-h-[220px] flex flex-col">
                    <textarea class="w-full h-full flex-1 bg-neutral-950 border border-neutral-800 focus:border-blue-500/50 rounded-lg p-3 font-mono text-xs text-neutral-350 placeholder:text-neutral-700 focus:outline-none resize-none" placeholder="Tempel berkas keamanan dari SIEM, Syslog firewall, Apache access log, atau Active Directory di sini..." x-model="logInput"></textarea>
                    <span class="absolute bottom-2 right-3 text-[9px] text-neutral-600 font-mono">UTF-8 ASCII logs allowed</span>
                </div>
            </div>

            <div x-show="error" x-cloak class="bg-red-500/10 border border-red-500/20 text-red-400 text-xs rounded p-3 flex gap-2 items-center font-mono">
                <span x-text="error"></span>
            </div>

            <button type="button" @click="analyze()" :disabled="loading" class="w-full bg-blue-650 hover:bg-blue-600 py-3 rounded text-xs font-black uppercase tracking-wider text-white transition-all cursor-pointer flex items-center justify-center gap-2 disabled:opacity-50">
                <span x-show="!loading">Mulai Investigasi Log Sekarang</span>
                <span x-show="loading" x-cloak>Mengevaluasi Kerangka Serangan AI...</span>
            </button>
        </div>

        <div class="lg:col-span-7 bg-neutral-950 border border-neutral-850/80 rounded-2xl p-5 flex flex-col min-h-[350px]">
            <div x-show="!result && !loading" class="flex-1 flex flex-col items-center justify-center text-center p-6">
                <div class="w-14 h-14 rounded bg-neutral-900 border border-neutral-800 flex items-center justify-center text-neutral-600 mb-4 font-mono">LOG</div>
                <p class="text-sm font-semibold text-neutral-300">Pusat Diagnosa AI Siap Digunakan</p>
                <p class="text-xs text-neutral-500 max-w-sm mt-2 font-serif">Pilih atau tempel salinan log keamanan di panel kiri lalu jalankan analisis untuk merekonstruksi investigasi forensik instan.</p>
            </div>
            <div x-show="loading" x-cloak class="flex-1 flex flex-col items-center justify-center text-center p-6">
                <div class="w-12 h-12 rounded-full border-2 border-blue-500/20 border-t-blue-500 animate-spin mb-4"></div>
                <p class="text-xs font-mono text-blue-400 uppercase tracking-widest terminal-cursor">MATA_SABER_SCANNING_SYSTEM</p>
                <p class="text-[11px] text-neutral-500 max-w-xs mt-2 font-mono uppercase tracking-wider">Pemrosesan kecerdasan kognitif LLM melalui jaringan asisten Website Informasi...</p>
            </div>
            <div x-show="result && !loading" x-cloak class="space-y-4 animate-fade-in flex-1 flex flex-col">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 font-mono">
                    <div class="bg-neutral-900 border border-neutral-800 rounded p-2.5"><span class="text-[9px] text-neutral-500 uppercase tracking-widest block font-bold">Klasifikasi</span><span class="text-xs font-bold text-white block mt-1 truncate" x-text="result?.classification || 'Unknown'"></span></div>
                    <div class="bg-neutral-900 border border-neutral-800 rounded p-2.5"><span class="text-[9px] text-neutral-500 uppercase tracking-widest block font-bold">Skor Ancaman</span><span class="text-[10px] font-bold px-2.5 py-0.5 rounded border mt-1 inline-block uppercase" :class="threatClass(result?.threatLevel)" x-text="result?.threatLevel || 'Unknown'"></span></div>
                    <div class="bg-neutral-900 border border-neutral-800 rounded p-2.5 col-span-2 sm:col-span-1"><span class="text-[9px] text-neutral-500 uppercase tracking-widest block font-bold">Mesin Core</span><span class="text-xs font-bold text-blue-400 block mt-1 uppercase">Gemini cognitive</span></div>
                </div>
                <div class="bg-neutral-900/60 border border-neutral-800/80 rounded-xl p-4.5">
                    <div class="text-blue-400 text-xs font-mono font-bold mb-2 uppercase tracking-widest">Analisis Intelegensi AI:</div>
                    <p class="text-[12.5px] text-neutral-300 leading-relaxed font-serif whitespace-pre-line" x-text="result?.analysis"></p>
                </div>
                <div class="bg-neutral-900/60 border border-neutral-800/80 rounded-xl p-4">
                    <div class="text-red-400 text-xs font-mono font-bold mb-2 uppercase tracking-widest">Indicators of Compromise (IoC):</div>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="ioc in result?.indicators || []"><span class="text-[10px] font-mono bg-red-950/30 text-red-400 border border-red-900/30 py-0.5 px-2.5 rounded font-bold" x-text="ioc"></span></template>
                    </div>
                </div>
                <div class="bg-neutral-900/60 border border-neutral-800/80 rounded-xl p-4.5 flex-1 flex flex-col">
                    <div class="text-blue-450 text-xs font-mono font-bold mb-3 uppercase tracking-widest">Defensive Playbook & Mitigation:</div>
                    <ol class="space-y-2.5 text-xs text-neutral-350 list-decimal list-inside flex-1 font-serif leading-relaxed">
                        <template x-for="step in result?.remediationSteps || []"><li><span class="font-serif pl-1" x-text="step"></span></li></template>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function logAnalyst() {
    return {
        logInput: '',
        result: null,
        loading: false,
        error: '',
        samples: [
            { name: 'SQL Injection Probe (Apache)', type: 'Apache Web Server', log: `192.168.1.45 - - [02/Jun/2026:10:24:15 +0700] "GET /api/v1/products.php?category=electronics' UNION SELECT ALL NULL,NULL,username,password_hash FROM app_users-- HTTP/1.1" 200 4520 "https://victim-web.com/prod" "Mozilla/5.0 (Kali-Linux; sqlmap/1.6.4)"` },
            { name: 'SSH Brute Force Attack (Linux)', type: 'Linux Auth Syslog', log: `Jun  2 03:15:22 gateway-srv sshd[28441]: Failed password for root from 185.220.101.4 port 38241 ssh2\nJun  2 03:15:24 gateway-srv sshd[28443]: Failed password for root from 185.220.101.4 port 38255 ssh2\nJun  2 03:15:26 gateway-srv sshd[28445]: Failed password for admin from 185.220.101.4 port 38269 ssh2\nJun  2 03:15:28 gateway-srv sshd[28447]: Failed password for backup from 185.220.101.4 port 38283 ssh2\nJun  2 03:15:30 gateway-srv sshd[28449]: Accepted password for root from 185.220.101.4 port 38301 ssh2` },
            { name: 'Windows lateral RDP (Security Log)', type: 'Windows Event Handlers', log: `<Event><System><Provider Name="Microsoft-Windows-Security-Auditing" /><EventID>4624</EventID></System><EventData><Data Name="TargetUserName">srv_backup_agent</Data><Data Name="TargetDomainName">CORP_ACTIVE_DIR</Data><Data Name="LogonType">10</Data><Data Name="IpAddress">10.0.12.89</Data></EventData></Event>` }
        ],
        async analyze() {
            if (!this.logInput.trim()) { this.error = 'Silakan masukkan teks log atau pilih salah satu sampel log di atas.'; return; }
            this.loading = true; this.error = ''; this.result = null;
            try {
                const response = await fetch('{{ route('gemini.analyze-log') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ logText: this.logInput, contextType: 'SOC Triage Laboratory' })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Respon server bermasalah saat mengirimkan log.');
                this.result = data;
            } catch (error) { this.error = error.message || 'Gagal menghubungi modul kognitif Gemini AI. Coba kembali.'; }
            finally { this.loading = false; }
        },
        threatClass(level) {
            const value = (level || '').toLowerCase();
            if (value === 'critical') return 'bg-purple-950/40 text-purple-400 border-purple-500/40 animate-pulse';
            if (value === 'high') return 'bg-red-950/40 text-red-400 border-red-500/40';
            if (value === 'medium') return 'bg-amber-950/40 text-amber-400 border-amber-500/40';
            return 'bg-blue-950/40 text-blue-400 border-blue-500/40';
        }
    };
}
</script>
@endpush
