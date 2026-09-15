<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sessions.show', $session) }}" class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Kembali">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Edit Session
            </h2>
        </div>
    </x-slot>

    <div class="max-w-3xl bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
        <form method="POST" action="{{ route('admin.sessions.update', $session) }}" class="space-y-5" data-disable-on-submit>
            @csrf
            @method('PUT')

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="title" :value="__('Judul Tugas')" />
                    <x-text-input id="title" class="mt-1 block w-full" type="text" name="title" :value="old('title', $session->title)" required autofocus />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="subject" :value="__('Mata Pelajaran')" />
                    <x-text-input id="subject" class="mt-1 block w-full" type="text" name="subject" :value="old('subject', $session->subject)" required />
                    <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="assignment_date" :value="__('Tanggal Tugas')" />
                    <x-text-input id="assignment_date" class="mt-1 block w-full" type="date" name="assignment_date" :value="old('assignment_date', $session->assignment_date->format('Y-m-d'))" required />
                    <x-input-error :messages="$errors->get('assignment_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="mt-1 block w-full border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                        @foreach (\App\Models\AssignmentSession::STATUSES as $status)
                            <option value="{{ $status }}" @selected(old('status', $session->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="description" :value="__('Deskripsi (opsional)')" />
                    <x-textarea-input id="description" class="mt-1 block w-full" rows="3" name="description">{{ old('description', $session->description) }}</x-textarea-input>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>

            <div class="flex items-start gap-2 text-sm text-slate-500 bg-slate-50 border border-slate-200 rounded-[10px] px-3 py-2.5">
                <i data-lucide="info" class="w-4 h-4 mt-0.5 shrink-0"></i>
                <p>Mengubah Source of Truth akan menyimpan versi baru (v{{ $session->currentSourceOfTruth?->version ?? 1 }} → v{{ ($session->currentSourceOfTruth?->version ?? 0) + 1 }}). Versi lama tetap tersimpan untuk audit.</p>
            </div>

            @include('admin.sessions._sot_editor', [
                'questions' => old('questions', $session->currentSourceOfTruth?->questions ?? []),
                'validationTypes' => \App\Models\SourceOfTruth::VALIDATION_TYPES,
            ])

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.sessions.show', $session) }}" class="inline-flex items-center bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-[10px] hover:bg-slate-50">Batal</a>
                <x-primary-button>Simpan Perubahan</x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
