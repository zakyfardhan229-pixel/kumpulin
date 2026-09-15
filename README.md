# Kumpulin!

Platform pengumpulan dan validasi tugas berbantuan AI. Siswa mengumpulkan foto tugas tanpa perlu akun, AI menganalisisnya berdasarkan Source of Truth dari admin, dan admin memegang keputusan final.

Prinsip utama: **Source of Truth → Rekomendasi AI → Keputusan Final Admin**. AI adalah asisten, bukan penilai.

## Fitur

**Siswa (tanpa login)**

- Buka session tugas via kode/link dari guru (`/submit/{kode}`)
- Isi identitas (nama, kelas, jurusan, catatan) dan upload foto tugas (JPG/JPEG/PNG/WEBP, maks. 10 MB)
- Terima Submission ID unik (`KMP-XXXXXXXX`) yang mudah disalin
- Cek status kapan saja via `/check` atau `/check/{id}` — lengkap dengan timeline visual
- Peringatan konfirmasi jika terdeteksi pengiriman ganda

**Admin**

- Dashboard statistik (sessions, menunggu review, diproses AI, selesai, perlu perbaikan)
- CRUD session tugas dengan kode unik (`TSK-INF-290926-X82K`) dan link pengumpulan yang bisa disalin
- Editor Source of Truth berversi — setiap perubahan menyimpan versi baru, versi lama tetap teraudit
- Tutup/arsipkan session (session tertutup tidak bisa menerima submission baru)
- Daftar submission dengan filter status/session/pencarian
- Halaman review split-view: foto tugas di kiri, analisis AI + keputusan di kanan
- Keputusan final: Selesai / Belum Selesai / Perlu Perbaikan + catatan (catatan tampil ke siswa khusus untuk Perlu Perbaikan)
- Retry analisis AI yang gagal — riwayat validasi AI dan review selalu disimpan, tidak pernah ditimpa

**AI**

- Analisis gambar tugas terhadap Source of Truth via `POST /api/chat` (multipart, field `files`, maks. 9 file)
- Respons berupa SSE streaming — token `answer` digabung lalu diparsing sebagai JSON terstruktur
- Berjalan sebagai Laravel Job (antrean `sync` saat dev, siap pindah ke `database`)
- Hasil AI (`likely_completed` / `likely_incomplete` / `uncertain`) tidak pernah mengubah status final secara langsung

## Alur Kerja

```text
Admin buat Session + Source of Truth
  → Sistem generate Session Code
    → Siswa buka session, isi identitas, upload foto
      → Sistem generate Submission ID (status: processing)
        → Job AI menganalisis (status: pending_review)
          → Admin review foto + analisis AI → keputusan final
            → Siswa cek status via Submission ID
```

Status yang dilihat siswa: `Diproses` → `Menunggu Review` → `Selesai` / `Belum Selesai` / `Perlu Perbaikan`.

## Tech Stack

- PHP 8.2, Laravel 12, Blade + Alpine.js
- Laravel Breeze (autentikasi admin), Eloquent ORM, Queue, Storage, HTTP Client
- Tailwind CSS v3, Lucide Icons, Vite
- MySQL/MariaDB (produksi/dev), SQLite in-memory (testing)
- Arnaru-AI (tanpa API key)

## Prasyarat

- PHP ^8.2 dengan ekstensi umum Laravel (openssl, pdo, mbstring, tokenizer, xml, ctype, json, fileinfo)
- Composer 2
- Node.js 20+ dan npm
- MySQL/MariaDB berjalan di `127.0.0.1:3306`

## Instalasi

```bash
git clone <url-repo> kumpulin
cd kumpulin

# Buat database MySQL kosong bernama 'kumpulin' terlebih dahulu,
# lalu sesuaikan kredensial di .env (lihat .env.example).

composer setup
```

`composer setup` menjalankan: `composer install`, salin `.env`, generate key, migrasi, `npm install`, dan `npm run build`.

### Konfigurasi `.env` penting

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kumpulin
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=sync   # ganti ke 'database' + jalankan queue:listen untuk async beneran

ARNARU_AI_BASE_URL=YOUR_ARNARU_URL
ARNARU_AI_MODEL=gpt-5.5
ARNARU_AI_TIMEOUT=120
```

### Akun admin default (hasil seeder, dev only)

```text
Email: admin@kumpulin.test
Password: password
```

## Menjalankan Aplikasi

```bash
composer dev
```

Perintah ini menjalankan 4 proses sekaligus: web server, queue listener, log viewer (pail), dan Vite HMR. Buka `http://localhost:8000`.

Perintah lain:

| Perintah | Kegunaan |
| --- | --- |
| `composer test` | Jalankan seluruh test (otomatis pakai SQLite in-memory) |
| `php artisan test --filter=NamaTest` | Jalankan satu test |
| `./vendor/bin/pint` | Format kode (Laravel preset) |
| `npm run build` | Build aset produksi |
| `php artisan migrate:fresh --seed` | Reset database + seed admin |

## Struktur Proyek (ringkas)

```text
app/
  Http/Controllers/
    SubmissionController.php        # form + upload publik siswa
    SubmissionStatusController.php  # cek status publik
    Admin/                          # dashboard, sessions, submissions, reviews
  Http/Requests/                    # validasi (StoreSubmissionRequest, dsb.)
  Jobs/ValidateSubmissionWithAI.php # panggil Arnaru-AI, simpan hasil
  Services/
    ArnaruAIService.php             # prompt + SSE parsing + normalisasi
    SessionCodeService.php          # generator TSK-XXXX-XXXXXX-XXXX
    SubmissionCodeService.php       # generator KMP-XXXXXXXX
  Models/                           # User, AssignmentSession, SourceOfTruth,
                                    # Submission, SubmissionFile, AiValidation, AdminReview
  DataTransferObjects/AiValidationResult.php
resources/views/
  layouts/      # admin (sidebar), student (publik), guest (auth)
  student/      # landing, submit, success, check, status, closed
  admin/        # dashboard, sessions/*, submissions/*
  components/   # status-badge, toast, textarea-input, dsb.
routes/web.php  # rute publik /submit/*, /check/* + grup /admin
```

## Skema Database

```text
users ──< assignment_sessions ──< source_of_truths (versioned)
                │
                └─< submissions ──< submission_files
                                ──< ai_validations (append-only)
                                ──< admin_reviews (append-only)
```

- Kode publik (`session_code`, `submission_code`) unik dan tidak berurutan — ID auto-increment tidak pernah diekspos.
- Setiap validasi AI menyimpan model, versi SoT yang dipakai, confidence, analisis, dan raw response untuk audit.

## Catatan Integrasi Arnaru-AI

- Endpoint `POST {base_url}/api/chat` tanpa autentikasi.
- Mode file memakai `multipart/form-data` dengan field teks `question`, `model`, `systemPrompt` dan file di field `files`.
- Respons selalu SSE: baris `data: {"success":true,"answer":"<token>"}` yang harus digabung hingga `data: [DONE]`, lalu diparsing sebagai JSON. Lihat `ArnaruAIService::extractAnswer()`.
- Semua kegagalan (timeout, HTTP error, JSON malformed) dicatat sebagai baris `ai_validations` berstatus `error`; submission tetap `processing` dan bisa di-retry dari halaman review.

## Testing

```bash
composer test
```

Test memakai SQLite `:memory:` + driver `array`/`sync` (lihat `phpunit.xml`), jadi MySQL tidak diperlukan. Mencakup: CRUD session + versioning SoT, alur submission siswa (termasuk duplikat dan session tertutup), halaman status, parsing SSE + normalisasi AI, job sukses/gagal/retry, review admin + aturan status final, dan catatan publik.

## Lisensi

MIT.
