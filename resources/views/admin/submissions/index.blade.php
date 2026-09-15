<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Submissions
        </h2>
    </x-slot>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="bg-white border border-slate-200 rounded-xl p-4">
        <form method="GET" action="{{ route('admin.submissions.index') }}" class="flex flex-col sm:flex-row gap-2">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau Submission ID..." class="w-full border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm pl-9">
            </div>
            <select name="session_id" class="border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                <option value="">Semua session</option>
                @foreach ($sessions as $s)
                    <option value="{{ $s->id }}" @selected((string) request('session_id') === (string) $s->id)>{{ $s->title }}</option>
                @endforeach
            </select>
            <select name="status" class="border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                <option value="">Semua status</option>
                @foreach (\App\Models\Submission::STATUSES as $st)
                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold px-4 py-2.5 rounded-[10px]">Filter</button>
        </form>
    </div>

    <div class="mt-4 bg-white border border-slate-200 rounded-xl overflow-hidden">
        @if ($submissions->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-primary-50 text-primary-600 flex items-center justify-center mb-4">
                    <i data-lucide="file-up" class="w-6 h-6"></i>
                </div>
                <h3 class="font-semibold text-slate-900">Belum ada tugas yang dikumpulkan.</h3>
                <p class="mt-1 text-sm text-slate-500">Submission siswa akan muncul di sini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                            <th class="px-4 py-3 font-semibold">Siswa</th>
                            <th class="px-4 py-3 font-semibold hidden md:table-cell">AI</th>
                            <th class="px-4 py-3 font-semibold">Status Admin</th>
                            <th class="px-4 py-3 font-semibold hidden sm:table-cell">Dikumpulkan</th>
                            <th class="px-4 py-3 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($submissions as $submission)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-900">{{ $submission->nama_lengkap }}</p>
                                    <p class="text-xs text-slate-500">{{ $submission->kelas }} · {{ $submission->jurusan }}</p>
                                    <p class="font-mono text-xs text-slate-500">{{ $submission->submission_code }}</p>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    @if ($submission->latestAiValidation && ! $submission->latestAiValidation->failed())
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600">
                                            <i data-lucide="sparkles" class="w-3.5 h-3.5 text-primary-500"></i>
                                            {{ ucfirst(str_replace('_', ' ', $submission->latestAiValidation->result)) }}
                                        </span>
                                    @elseif ($submission->latestAiValidation?->failed())
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-red-600">
                                            <i data-lucide="circle-x" class="w-3.5 h-3.5"></i> AI gagal
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3"><x-status-badge :status="$submission->status" /></td>
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap hidden sm:table-cell">{{ $submission->created_at->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.submissions.show', $submission) }}" class="inline-flex items-center gap-1 text-primary-600 hover:text-primary-700 font-medium">Review <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-slate-200">
                {{ $submissions->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
