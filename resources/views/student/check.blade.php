<x-student-layout>
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-[10px] bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                <i data-lucide="search" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg text-slate-900 leading-tight">Cek Status Tugas</h1>
                <p class="text-sm text-slate-500">Masukkan Submission ID yang kamu terima.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('student.check') }}" class="mt-4 space-y-3">
            <div>
                <x-input-label for="code" :value="__('Submission ID')" />
                <x-text-input id="code" class="mt-1 block w-full font-mono uppercase" type="text" name="code" :value="old('code')" required autofocus placeholder="KMP-XXXXXXXX" />
            </div>
            <x-primary-button class="w-full justify-center">
                <i data-lucide="search" class="w-4 h-4 mr-1.5"></i>
                Cek Status
            </x-primary-button>
        </form>
    </div>
</x-student-layout>
