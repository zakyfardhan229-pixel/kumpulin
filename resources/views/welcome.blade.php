<x-student-layout>
    <div class="bg-white border border-slate-200 rounded-xl p-6 sm:p-8 text-center">
        <div class="mx-auto w-14 h-14 rounded-[10px] bg-primary-600 text-white flex items-center justify-center">
            <i data-lucide="layers" class="w-7 h-7"></i>
        </div>
        <h1 class="mt-4 font-bold text-2xl text-slate-900">Kumpulin!</h1>
        <p class="mt-1 text-sm text-slate-500">Kumpulkan tugas dengan mudah.<br>Pantau statusnya kapan saja.</p>

        <form method="GET" action="{{ route('home') }}" class="mt-6 text-left space-y-3">
            <div>
                <x-input-label for="session" :value="__('Kode Session dari guru')" />
                <x-text-input id="session" class="mt-1 block w-full font-mono uppercase" type="text" name="session" placeholder="TSK-INF-290926-X82K" />
            </div>
            <x-primary-button class="w-full justify-center">
                <i data-lucide="upload" class="w-4 h-4 mr-1.5"></i>
                Mulai Kumpulkan
            </x-primary-button>
        </form>

        <div class="my-4 flex items-center gap-3 text-xs text-slate-400">
            <span class="flex-1 border-t border-slate-200"></span>
            atau
            <span class="flex-1 border-t border-slate-200"></span>
        </div>

        <a href="{{ route('student.check') }}" class="inline-flex items-center justify-center gap-1.5 w-full bg-white border border-slate-200 text-slate-700 text-sm font-semibold px-4 py-2.5 rounded-[10px] hover:bg-slate-50">
            <i data-lucide="search" class="w-4 h-4"></i>
            Cek Pengumpulan
        </a>
    </div>
</x-student-layout>
