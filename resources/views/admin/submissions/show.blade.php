<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.submissions.index') }}" class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Kembali">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-slate-800 leading-tight font-mono">{{ $submission->submission_code }}</h2>
                <p class="text-sm text-slate-500">{{ $submission->session->title }} · Dikumpulkan {{ $submission->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid lg:grid-cols-2 gap-4 items-start">
        <!-- Left: assignment image -->
        <div class="bg-white border border-slate-200 rounded-xl p-4">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Foto Tugas</h3>
            @if ($submission->files->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($submission->files as $file)
                        <a href="{{ route('admin.files.show', $file) }}" target="_blank" class="block">
                            <img src="{{ route('admin.files.show', $file) }}" alt="Foto tugas {{ $submission->nama_lengkap }}" class="w-full rounded-[10px] border border-slate-200" loading="lazy">
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Tidak ada file.</p>
            @endif
        </div>

        <!-- Right: info + AI + decision -->
        <div class="space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-bold text-slate-900">{{ $submission->nama_lengkap }}</p>
                        <p class="text-sm text-slate-500">{{ $submission->kelas }} · {{ $submission->jurusan }}</p>
                        @if ($submission->catatan)
                            <p class="mt-2 text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-[10px] px-3 py-2">“{{ $submission->catatan }}”</p>
                        @endif
                    </div>
                    <x-status-badge :status="$submission->status" class="shrink-0" />
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5">
                <div class="flex items-center gap-1.5">
                    <i data-lucide="sparkles" class="w-4 h-4 text-primary-500"></i>
                    <h3 class="font-semibold text-slate-900 text-sm">Analisis AI</h3>
                </div>

                @php($ai = $submission->latestAiValidation)
                @if (! $ai)
                    <p class="mt-2 text-sm text-slate-500">Belum ada analisis AI.</p>
                @elseif ($ai->failed())
                    <div class="mt-2 flex items-start gap-2 text-sm bg-red-50 border border-red-200 text-red-700 rounded-[10px] px-3 py-2.5">
                        <i data-lucide="circle-x" class="w-4 h-4 mt-0.5 shrink-0"></i>
                        <p>Analisis AI gagal: {{ $ai->raw_response }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.submissions.retry', $submission) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Coba Lagi
                        </button>
                    </form>
                @else
                    <p class="mt-2 text-sm font-bold text-slate-900 uppercase tracking-wide">{{ str_replace('_', ' ', $ai->result) }}</p>
                    <p class="text-xs text-slate-500">{{ number_format($ai->confidence * 100, 0) }}% confidence · model {{ $ai->model }} · SoT v{{ $ai->source_of_truth_version }}</p>
                    @if (! empty($ai->analysis['questions']))
                        <ul class="mt-3 space-y-2 text-sm">
                            @foreach ($ai->analysis['questions'] as $q)
                                <li class="border border-slate-100 rounded-[10px] p-2.5">
                                    <p class="font-semibold text-slate-800 text-xs">
                                        Soal {{ $q['number'] ?? '?' }} ·
                                        <span class="{{ ($q['status'] ?? '') === 'correct' ? 'text-green-600' : 'text-amber-600' }}">{{ ucfirst($q['status'] ?? 'unknown') }}</span>
                                        <span class="font-normal text-slate-400">({{ number_format(($q['confidence'] ?? 0) * 100, 0) }}%)</span>
                                    </p>
                                    @if (! empty($q['reason']))
                                        <p class="mt-0.5 text-slate-600 text-xs">{{ $q['reason'] }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if (! empty($ai->analysis['summary']))
                        <p class="mt-2 text-sm text-slate-600">{{ $ai->analysis['summary'] }}</p>
                    @endif
                    @if ($submission->aiValidations->count() > 1)
                        <p class="mt-1 text-xs text-slate-400">{{ $submission->aiValidations->count() }} riwayat analisis tersimpan.</p>
                    @endif
                @endif
                <p class="mt-2 text-xs text-slate-400">Rekomendasi AI bukan keputusan final.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-5">
                <h3 class="font-semibold text-slate-900 text-sm">Keputusan Admin</h3>
                <form method="POST" action="{{ route('admin.submissions.review', $submission) }}" class="mt-3 space-y-3" onsubmit="return confirm('Simpan keputusan ini?')">
                    @csrf
                    <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Keputusan">
                        @foreach (['completed' => ['Selesai', 'circle-check', 'peer-checked:border-green-600 peer-checked:bg-green-50 peer-checked:text-green-700'], 'incomplete' => ['Belum Selesai', 'circle-x', 'peer-checked:border-red-600 peer-checked:bg-red-50 peer-checked:text-red-700'], 'revision_required' => ['Perlu Perbaikan', 'triangle-alert', 'peer-checked:border-orange-500 peer-checked:bg-orange-50 peer-checked:text-orange-700']] as $value => [$label, $icon, $active])
                            <label class="cursor-pointer">
                                <input type="radio" name="decision" value="{{ $value }}" class="peer sr-only" @checked(old('decision', $submission->latestReview?->decision) === $value) required>
                                <span class="flex flex-col items-center gap-1 border border-slate-200 rounded-[10px] px-2 py-3 text-xs font-semibold text-slate-600 hover:bg-slate-50 {{ $active }}">
                                    <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('decision')" />
                    <div>
                        <x-input-label for="note" :value="__('Catatan admin (ditampilkan ke siswa jika Perlu Perbaikan)')" />
                        <x-textarea-input id="note" class="mt-1 block w-full" rows="3" name="note" placeholder="Contoh: Nomor 3 belum lengkap.">{{ old('note', $submission->latestReview?->note) }}</x-textarea-input>
                        <x-input-error :messages="$errors->get('note')" />
                    </div>
                    <x-primary-button class="w-full justify-center">Simpan Validasi</x-primary-button>
                </form>

                @if ($submission->adminReviews->isNotEmpty())
                    <details class="mt-3 text-sm">
                        <summary class="cursor-pointer font-medium text-slate-600">Riwayat review ({{ $submission->adminReviews->count() }})</summary>
                        <ul class="mt-2 space-y-1.5 text-slate-500 text-xs">
                            @foreach ($submission->adminReviews as $review)
                                <li>{{ $review->reviewed_at->format('d M Y H:i') }} · {{ $review->admin->name }} · <strong>{{ str_replace('_', ' ', $review->decision) }}</strong>@if ($review->note) — {{ $review->note }}@endif</li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
