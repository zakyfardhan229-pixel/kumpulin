<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sessions.index') }}" class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Kembali">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight">{{ $session->title }}</h2>
                <p class="text-sm text-slate-500">{{ $session->subject }} · {{ $session->assignment_date->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Session Code</p>
                        <p class="font-mono text-xl font-bold text-slate-900">{{ $session->session_code }}</p>
                    </div>
                    <x-status-badge :status="$session->status" />
                </div>
                @if ($session->description)
                    <p class="mt-3 text-sm text-slate-600">{{ $session->description }}</p>
                @endif
                <div class="mt-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500 font-semibold">Link Pengumpulan</p>
                    <div class="mt-1 flex items-center gap-2">
                        <code id="session-link" class="flex-1 min-w-0 truncate text-sm bg-slate-50 border border-slate-200 rounded-[10px] px-3 py-2">{{ url('/submit/'.$session->session_code) }}</code>
                        <button type="button" data-copy-target="session-link" class="copy-btn inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700 border border-primary-100 bg-primary-50 rounded-[10px] px-3 py-2">
                            <i data-lucide="copy" class="w-4 h-4"></i> Salin
                        </button>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.sessions.edit', $session) }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-[10px] hover:bg-slate-50">
                        <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                    </a>
                    @if ($session->isActive())
                        <form method="POST" action="{{ route('admin.sessions.close', $session) }}" onsubmit="return confirm('Tutup session ini? Siswa tidak bisa lagi mengumpulkan tugas.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-[10px] hover:bg-slate-50">
                                <i data-lucide="lock" class="w-4 h-4"></i> Tutup Session
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-slate-900">Source of Truth</h3>
                    <span class="text-xs font-semibold text-slate-500">v{{ $session->currentSourceOfTruth?->version ?? '—' }}</span>
                </div>
                @if ($session->currentSourceOfTruth)
                    <ol class="mt-3 space-y-3">
                        @foreach ($session->currentSourceOfTruth->questions as $i => $q)
                            <li class="border border-slate-100 rounded-[10px] p-3 text-sm">
                                <p class="font-semibold text-slate-900">Soal {{ $i + 1 }} <span class="ml-1 font-normal text-xs text-slate-500">· {{ ucfirst($q['validation_type'] ?? '') }}</span></p>
                                <p class="mt-1 text-slate-700">{{ $q['question'] ?? '' }}</p>
                                <p class="mt-1 text-slate-500"><span class="font-medium">Diharapkan:</span> {{ $q['expected'] ?? '' }}</p>
                                @if (! empty($q['required_concepts']))
                                    <p class="mt-1 text-slate-500"><span class="font-medium">Konsep:</span> {{ implode(', ', $q['required_concepts']) }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if ($session->sourceOfTruths->count() > 1)
                        <details class="mt-3 text-sm">
                            <summary class="cursor-pointer font-medium text-slate-600">Riwayat versi ({{ $session->sourceOfTruths->count() }})</summary>
                            <ul class="mt-2 space-y-1 text-slate-500">
                                @foreach ($session->sourceOfTruths as $sot)
                                    <li>v{{ $sot->version }} · {{ count($sot->questions ?? []) }} soal · {{ $sot->created_at->format('d M Y H:i') }}</li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                @else
                    <p class="mt-2 text-sm text-slate-500">Belum ada Source of Truth.</p>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
                <h3 class="font-semibold text-slate-900">Statistik</h3>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Total submission</dt><dd class="font-semibold">{{ $stats['total'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Diproses AI</dt><dd class="font-semibold">{{ $stats['processing'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Menunggu review</dt><dd class="font-semibold">{{ $stats['pending'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Selesai</dt><dd class="font-semibold">{{ $stats['completed'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Belum selesai</dt><dd class="font-semibold">{{ $stats['incomplete'] }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Perlu perbaikan</dt><dd class="font-semibold">{{ $stats['revision'] }}</dd></div>
                </dl>
                <a href="{{ route('admin.submissions.index', ['session_id' => $session->id]) }}" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700">
                    Lihat submission <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.copy-btn').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const el = document.getElementById(btn.dataset.copyTarget);
            const text = el.textContent.trim();
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {
                const range = document.createRange();
                range.selectNodeContents(el);
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
                document.execCommand('copy');
                sel.removeAllRanges();
            }
            const label = btn.lastChild;
            const original = label.textContent;
            label.textContent = ' Disalin!';
            setTimeout(() => { label.textContent = original; }, 1500);
        });
    });
    </script>
</x-admin-layout>
