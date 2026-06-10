<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Mendeteksi Lateral Movement Menggunakan Active Directory Security Log Event ID 4624',
                'summary' => 'Panduan praktis deteksi intrusi menggunakan analisis Logon Type pada Windows Security event log untuk menghentikan pergerakan hacker di jaringan.',
                'content' => <<<'MARKDOWN'
### Pendahuluan
Lateral movement adalah taktik kritis di mana penyerang menggunakan kredensial yang disusupi untuk menjelajahi lingkungan internal target, mendapatkan akses ke aset penting, dan akhirnya menguasai Domain Controller. Sebagai analis keamanan, melacak taktik ini memerlukan pemahaman mendalam tentang log audit Windows.

### Melacak Event ID 4624: Logon Success
Setiap kali autentikasi berhasil pada endpoint Windows, **Event ID 4624** dibuat di dalam Security log. Kunci utama untuk mendeteksi pergerakan lateral yang mencurigakan adalah **Logon Type**.

| Logon Type | Nama Deskriptif | Deskripsi Deteksi |
| :--- | :--- | :--- |
| **Logon Type 2** | Interactive | Pengguna masuk secara fisik (keyboard/layar langsung). Mencurigakan di server produksi jam 2 pagi. |
| **Logon Type 3** | Network | Akses melalui network share seperti SMB (paling sering digunakan oleh alat pentesting seperti PsExec atau malware lateral). |
| **Logon Type 10** | RDP (Remote Desktop) | Pengguna melakukan koneksi desktop jarak jauh. Periksa anomali histori IP sumber. |

### Skenario Deteksi Praktis
1. **Pendeteksian PsExec / Impacket wmiexec:**
   Ketika penyerang mengeksekusi perintah jarak jauh, Anda akan melihat Event ID 4624 dengan **Logon Type 3** disusul dengan pembuatan service baru (**Event ID 7045** di System Log) atau proses command prompt yang diluncurkan oleh `WmiPrvSE.exe`.
   
2. **Kueri PowerShell Deteksi:**
   Analis keamanan dapat menjalankan skrip PowerShell berikut untuk mengekstrak log autentikasi tipe jaringan yang mencurigakan:
   ```powershell
   Get-WinEvent -FilterHashtable @{LogName='Security';ID=4624} | 
     Where-Object {$_.Properties[8].Value -eq 3 -and $_.Properties[18].Value -ne "-"} |
     Select-Object TimeCreated, 
                   @{N='TargetUser';E={$_.Properties[5].Value}},
                   @{N='IPSource';E={$_.Properties[18].Value}} | 
     Format-Table -AutoSize
   ```

### Langkah Remediasi
Jika anomali terdeteksi:
- Isolasi host sumber dan host target dari jaringan menggunakan endpoint firewall.
- Reset kata sandi akun pengguna yang disusupi di Active Directory.
- Lakukan penarikan RAM dump dari host target untuk analisa volatile memory lebih lanjut.
MARKDOWN,
                'image_url' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
                'author' => 'Zacky Ramadhan (SOC Analyst Lead)',
                'published_date' => '2026-05-15',
                'category' => 'Incident Response',
                'tags' => ['incident-response', 'windows-security', 'active-directory', 'threat-hunting'],
            ],
            [
                'title' => 'Linux Server Hardening: Kebijakan SSH Terbaik untuk Menghentikan Serangan Brute Force',
                'summary' => 'Konfigurasi terpadu berkas sshd_config untuk memitigasi akses tidak sah dan implementasi multi-layer pertahanan cyber pada server Linux.',
                'content' => <<<'MARKDOWN'
### Tantangan Keamanan SSH
Mesin publik di internet terus-menerus dipindai oleh botnet yang mencoba kredensial administratif default melalui SSH. Sebagai administrator server atau profesional keamanan, mengabaikan proteksi SSH default berisiko fatal pada keamanan sistem.

### Langkah Hardening Langkah-demi-Langkah

#### 1. Modifikasi Berkas Konfigurasi SSH
Akses server, kemudian edit berkas konfigurasi menggunakan editor teks favorit Anda:
```bash
sudo nano /etc/ssh/sshd_config
```

Terapkan parameter pengetatan konfigurasi berikut:

```ini
# Ganti port default (22) ke port non-standar untuk menghindari port-scanning massal
Port 6122

# Batasi protokol hanya ke versi terbaru
Protocol 2

# Larang masuk menggunakan akun administrator utama (root) langsung
PermitRootLogin no

# Batasi jumlah percobaan sandi yang salah sebelum koneksi diputus
MaxAuthTries 3

# Nonaktifkan autentikasi berbasis sandi biasa. Wajibkan penggunaan SSH Key!
PasswordAuthentication no
PubkeyAuthentication yes

# Nonaktifkan login tanpa kata sandi
PermitEmptyPasswords no

# Tampilan pesan peringatan hukum (Banner) sebelum akses
Banner /etc/issue.net
```

Simpan dan lakukan validasi sintaks sebelum melakukan restart service:
```bash
sudo sshd -t
sudo systemctl restart ssh
```

#### 2. Pasang Fail2ban untuk Mitigasi Real-time
Fail2ban memantau log autentikasi (`/var/log/auth.log`), mendeteksi upaya brute force, dan memblokir alamat IP penyerang secara dinamis via IPtables.

Instalasi pada distro berbasis Debian/Ubuntu:
```bash
sudo apt update && sudo apt install fail2ban -y
```

Buat berkas konfigurasi lokal `/etc/fail2ban/jail.local`:
```ini
[sshd]
enabled = true
port = 6122
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 86400  # Blokir IP selama 24 jam
```

Restart service Fail2ban:
```bash
sudo systemctl restart fail2ban
```

### Kesimpulan
Dengan memblokir port default, mematikan otentikasi password, dan menginstal auto-ban, Anda menghilangkan lebih dari 99% serangan berulang otomatis di internet.
MARKDOWN,
                'image_url' => 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?auto=format&fit=crop&w=800&q=80',
                'author' => 'Amanda Putri (SecOps Engineer)',
                'published_date' => '2026-05-22',
                'category' => 'System Hardening',
                'tags' => ['system-hardening', 'linux', 'ssh-hardening', 'fail2ban'],
            ],
            [
                'title' => 'Analisis Forensik Digital: Melacak Jejak Persistensi Malware Ransomware Melalui Registry Windows',
                'summary' => 'Eksplorasi forensik digital pada artifak Windows Registry untuk merekonstruksi jejak persistensi malware yang berupaya berjalan otomatis saat booting.',
                'content' => <<<'MARKDOWN'
### Pendahuluan
Ketika insiden infeksi ransomware melanda, tim tanggap insiden (**Incident Response**) tidak hanya fokus pada pemulihan file, tetapi juga pada penghentian infeksi berulang. Salah satu cara malware memastikan kehadirannya kembali setelah sistem dinyalakan ulang adalah melalui teknik **Persistence**.

### Lokasi Registry Runs Utama
Analis **Digital Forensics** biasanya memeriksa beberapa titik kunci di Windows Registry untuk mencari entri startup yang tidak sah:

#### Kueri Run Kunci Pengguna Tunggal (User Specific)
```
HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\Run
HKEY_CURRENT_USER\Software\Microsoft\Windows\CurrentVersion\RunOnce
```

#### Kueri Run Kunci Mesin Global (System Wide)
```
HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Run
HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\RunOnce
```

### Taktik Tersembunyi: Userinit & Winlogon Helper
Malware canggih sering menghindari folder `Run` konvensional dan malah memodifikasi nilai autentikasi default:
- Lokasi: `HKLM\SOFTWARE\Microsoft\Windows NT\CurrentVersion\Winlogon`
- Nilai Default: `Userinit.exe`
- Manipulasi Malware: Nilai diubah menjadi `userinit.exe, C:\ProgramData\malicious_payload.exe` yang meluncurkan malware langsung disaat proses logon Windows.

### Kueri Pencarian Forensik Menggunakan CMD
Analis forensik dapat menggunakan perintah bawaan `reg` untuk melihat entri yang aktif berjalan di startup tanpa merusak integritas sistem:
```cmd
reg query "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" /s
```

### Langkah Penanganan (Forensic Triage)
1. **Ambil Snapshot Memory Volatile:** Selalu lakukan dump RAM sebelum mematikan mesin agar proses malware yang berjalan tidak hilang.
2. **Ekspor Hive Registry:** Ambil berkas `SYSTEM`, `SOFTWARE`, dan `NTUSER.DAT` untuk dianalisis secara offline menggunakan alat canggih seperti **Registry Explorer**.
3. **Analisa Timestamp Amandemen:** Registry Windows menyimpan metadata tentang kapan kueri terakhir dimodifikasi, membantu penentuan rentang waktu insiden (timeline analysis).
MARKDOWN,
                'image_url' => 'https://images.unsplash.com/photo-1601597111158-2fceff270190?auto=format&fit=crop&w=800&q=80',
                'author' => 'Ryan Setiadi (Digital Forensics Lead)',
                'published_date' => '2026-05-28',
                'category' => 'Digital Forensics',
                'tags' => ['digital-forensics', 'malware-analysis', 'windows-registry', 'incident-handling'],
            ],
        ];

        foreach ($articles as $article) {
            Article::updateOrCreate(['title' => $article['title']], $article);
        }
    }
}
