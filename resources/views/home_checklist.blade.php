<div id="hardening-checklist-panel" class="bg-neutral-900/40 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-xl relative overflow-hidden" x-data="hardeningChecklist()">
    <div class="absolute top-0 left-0 w-48 h-48 bg-blue-500/5 rounded-full blur-3xl -z-10"></div>
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-neutral-850 pb-5 mb-5">
        <div>
            <h3 class="text-lg font-serif italic text-white flex items-center gap-2">Audit Kepatuhan Hardening Server</h3>
            <p class="text-xs text-neutral-450 mt-1 font-serif">Materi taktis untuk mengamankan dan mensertifikasi level benteng pertahanan Windows dan Linux.</p>
        </div>
        <button type="button" @click="reset()" class="self-start sm:self-center flex items-center gap-1.5 bg-neutral-950 hover:bg-neutral-850 text-xs font-bold uppercase tracking-wider text-neutral-400 hover:text-white py-2 px-3.5 rounded border border-neutral-800 transition-all cursor-pointer font-mono">Reset Audit</button>
    </div>
    <div class="bg-neutral-950 border border-neutral-850 rounded-2xl p-4.5 mb-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
        <div class="md:col-span-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full border-4 flex items-center justify-center font-bold text-sm" :class="progress > 70 ? 'border-blue-500 text-blue-400 bg-blue-950/20' : progress > 40 ? 'border-amber-500 text-amber-400 bg-amber-950/20' : 'border-neutral-500 text-neutral-400 bg-neutral-900/20'"><span x-text="progress + '%'"></span></div>
            <div><span class="text-xs font-semibold text-white block">Skor Kepatuhan Keamanan</span><span class="text-[10px] text-neutral-500 font-mono block mt-0.5"><span x-text="complete"></span> dari <span x-text="items.length"></span> kebijakan diterapkan</span></div>
        </div>
        <div class="md:col-span-8">
            <div class="w-full bg-neutral-900 rounded-full h-2 overflow-hidden border border-neutral-850"><div class="h-full rounded-full transition-all duration-500" :class="progress > 70 ? 'bg-blue-500' : progress > 40 ? 'bg-amber-500' : 'bg-neutral-550'" :style="`width: ${progress}%`"></div></div>
            <p class="text-[10.5px] text-neutral-450 mt-2.5 italic font-serif" x-text="progress === 100 ? 'Sempurna! Seluruh postur audit pertahanan berhasil diperketat.' : progress > 50 ? 'Bagus! Sistem Anda mulai terbentengi. Terapkan sisa mitigasi penting.' : 'Postur pertahanan rentan. Segera mitigasi item dengan badge Critical.'"></p>
        </div>
    </div>
    <div class="flex gap-2 mb-4 overflow-x-auto pb-1.5 font-mono">
        <template x-for="os in ['All', 'Windows', 'Linux']">
            <button type="button" @click="filterOS = os" class="py-1.5 px-3.5 rounded text-xs font-bold uppercase tracking-wider transition-all cursor-pointer" :class="filterOS === os ? 'bg-blue-650 text-white' : 'bg-neutral-950 text-neutral-400 hover:text-white border border-neutral-850'" x-text="os === 'All' ? 'Semua Platform' : os + ' Only'"></button>
        </template>
    </div>
    <div class="space-y-3">
        <template x-for="item in filtered" :key="item.id">
            <div @click="item.status = !item.status" class="flex items-start gap-4 p-4 bg-neutral-950/40 hover:bg-neutral-950 border rounded-2xl transition-all cursor-pointer select-none" :class="item.status ? 'border-blue-500/25 bg-blue-950/5 hover:border-blue-500/35' : 'border-neutral-850 hover:border-neutral-750'">
                <button type="button" class="mt-0.5 text-neutral-500 hover:text-white focus:outline-none shrink-0"><span class="text-xl" :class="item.status ? 'text-blue-400' : 'text-neutral-700'" x-text="item.status ? '●' : '○'"></span></button>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="text-xs font-bold text-white font-sans" x-text="item.title"></span>
                        <span class="text-[9px] font-mono font-bold bg-neutral-900 text-neutral-450 border border-neutral-800 rounded px-1.5 py-0.5 uppercase" x-text="item.os"></span>
                        <span class="text-[9px] font-mono font-bold border rounded px-1.5 py-0.5 uppercase" :class="severityClass(item.severity)" x-text="item.severity"></span>
                        <span class="text-[9.5px]/none font-mono text-neutral-500 bg-neutral-900/60 rounded px-1.5 pb-0.5 border border-neutral-850 ml-auto hidden sm:inline-block" x-text="item.category"></span>
                    </div>
                    <p class="text-xs text-neutral-400 leading-relaxed font-serif" x-text="item.description"></p>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function hardeningChecklist() {
    const initial = [
        { id: 'hc-1', title: 'Nonaktifkan SSH Password Authentication', category: 'Access Control', os: 'Linux', severity: 'Critical', description: "Ubah 'PasswordAuthentication no' di sshd_config dan wajibkan autentikasi menggunakan kunci SSH publik.", status: true },
        { id: 'hc-2', title: 'Batasi Akses Administrator Remote (RDP)', category: 'Network Defense', os: 'Windows', severity: 'High', description: 'Terapkan Network Level Authentication (NLA) dan batasi IP pengakses RDP hanya dari subnet VPN internal.', status: false },
        { id: 'hc-3', title: 'Nonaktifkan Protokol SMBv1', category: 'Protocol Hardening', os: 'Windows', severity: 'Critical', description: 'SMB versi 1 rentan terhadap eksploitasi Remote Code Execution (seperti EternalBlue). Matikan fitur ini via PowerShell.', status: true },
        { id: 'hc-4', title: 'Instal dan Konfigurasikan UFW/IPTables', category: 'Perimeter', os: 'Linux', severity: 'High', description: 'Tutup semua port masuk yang tidak digunakan (default DENY incoming, ALLOW outgoing). Hanya izinkan lalu lintas esensial.', status: false },
        { id: 'hc-5', title: 'Aktifkan Windows Defender Antivirus / Tamper Protection', category: 'Endpoint', os: 'Windows', severity: 'High', description: 'Terapkan kebijakan pertahanan tamper-proofing untuk menghalangi aktor ransomware menghentikan pertahanan lokal.', status: true },
        { id: 'hc-6', title: 'Terapkan Kebijakan Minimisasi Sudoers', category: 'Access Control', os: 'Linux', severity: 'Medium', description: "Audit akun di berkas '/etc/sudoers' untuk memastikan prinsip hak istimewa paling rendah (Principle of Least Privilege).", status: false },
        { id: 'hc-7', title: 'Aktifkan Audit Sukses & Gagal pada Windows Logon', category: 'Audit & Logging', os: 'Windows', severity: 'Medium', description: 'Konfigurasikan Audit Policy untuk mencatat seluruh peristiwa Event ID 4624 (Logon Sukses) dan 4625 (Logon Gagal).', status: false }
    ];
    return {
        items: JSON.parse(JSON.stringify(initial)),
        filterOS: 'All',
        get complete() { return this.items.filter(item => item.status).length; },
        get progress() { return Math.round((this.complete / this.items.length) * 100); },
        get filtered() { return this.filterOS === 'All' ? this.items : this.items.filter(item => item.os === this.filterOS); },
        reset() { this.items = initial.map(item => ({ ...item, status: false })); },
        severityClass(sev) {
            if (sev === 'Critical') return 'bg-red-500/10 text-red-400 border-red-500/20';
            if (sev === 'High') return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
            return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
        }
    };
}
</script>
@endpush
