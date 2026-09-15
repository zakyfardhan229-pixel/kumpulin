<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sessions.index') }}" class="p-2 -ml-2 rounded-lg text-slate-500 hover:bg-slate-100" aria-label="Kembali">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Buat Session
            </h2>
        </div>
    </x-slot>

    <div class="max-w-3xl bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
        <form method="POST" action="{{ route('admin.sessions.store') }}" class="space-y-5" data-disable-on-submit>
            @csrf

            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input-label for="title" :value="__('Judul Tugas')" />
                    <x-text-input id="title" class="mt-1 block w-full" type="text" name="title" :value="old('title')" required autofocus placeholder="Tugas Informatika" />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="subject" :value="__('Mata Pelajaran')" />
                    <x-text-input id="subject" class="mt-1 block w-full" type="text" name="subject" :value="old('subject')" required placeholder="Informatika" />
                    <x-input-error :messages="$errors->get('subject')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="assignment_date" :value="__('Tanggal Tugas')" />
                    <x-text-input id="assignment_date" class="mt-1 block w-full" type="date" name="assignment_date" :value="old('assignment_date')" required />
                    <x-input-error :messages="$errors->get('assignment_date')" class="mt-1" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="description" :value="__('Deskripsi (opsional)')" />
                    <x-textarea-input id="description" class="mt-1 block w-full" rows="3" name="description" placeholder="Petunjuk pengerjaan untuk siswa...">{{ old('description') }}</x-textarea-input>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>

            @include('admin.sessions._sot_editor', [
                'questions' => old('questions', [['question' => '', 'expected' => '', 'validation_type' => 'semantic', 'required_concepts' => '']]),
                'validationTypes' => \App\Models\SourceOfTruth::VALIDATION_TYPES,
            ])

            <div class="flex justify-end gap-2 pt-2">
                <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-[10px] hover:bg-slate-50">Batal</a>
                <x-primary-button>Buat Session</x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
