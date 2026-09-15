<x-student-layout>
    <div class="bg-white border border-slate-200 rounded-xl p-4 sm:p-6">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-[10px] bg-primary-50 text-primary-600 flex items-center justify-center shrink-0">
                <i data-lucide="book-open" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <h1 class="font-bold text-lg text-slate-900 leading-tight">{{ $session->title }}</h1>
                <p class="text-sm text-slate-500">{{ $session->subject }} · {{ $session->assignment_date->format('d M Y') }}</p>
            </div>
        </div>
        @if ($session->description)
            <p class="mt-3 text-sm text-slate-600">{{ $session->description }}</p>
        @endif
    </div>

    @if (session('duplicate_warning'))
        <div class="mt-4 flex items-start gap-2 text-sm bg-amber-50 border border-amber-200 text-amber-800 rounded-[10px] px-3 py-2.5" role="alert">
            <i data-lucide="triangle-alert" class="w-4 h-4 mt-0.5 shrink-0"></i>
            <p>Kamu sudah mengirim tugas untuk session ini. Pilih ulang foto tugasmu, lalu tekan <strong>Kumpulkan Tugas</strong> lagi jika yakin ingin mengirim ulang.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('student.submit.store', $session->session_code) }}" enctype="multipart/form-data" class="mt-4 bg-white border border-slate-200 rounded-xl p-4 sm:p-6 space-y-4" id="submit-form">
        @csrf
        @if (session('duplicate_warning'))
            <input type="hidden" name="confirm_resubmit" value="1">
        @endif

        <div>
            <x-input-label for="nama_lengkap" :value="__('Nama Lengkap')" />
            <x-text-input id="nama_lengkap" class="mt-1 block w-full" type="text" name="nama_lengkap" :value="old('nama_lengkap')" required autocomplete="name" placeholder="Nama lengkap kamu" />
            <x-input-error :messages="$errors->get('nama_lengkap')" class="mt-1" />
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label for="kelas" :value="__('Kelas')" />
                <x-text-input id="kelas" class="mt-1 block w-full" type="text" name="kelas" :value="old('kelas')" required placeholder="X RPL 1" />
                <x-input-error :messages="$errors->get('kelas')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="jurusan" :value="__('Jurusan')" />
                <x-text-input id="jurusan" class="mt-1 block w-full" type="text" name="jurusan" :value="old('jurusan')" required placeholder="RPL" />
                <x-input-error :messages="$errors->get('jurusan')" class="mt-1" />
            </div>
        </div>

        <div>
            <x-input-label for="catatan" :value="__('Catatan (opsional)')" />
            <x-textarea-input id="catatan" class="mt-1 block w-full" rows="2" name="catatan" placeholder="Contoh: soal nomor 3 dikerjakan di halaman 2">{{ old('catatan') }}</x-textarea-input>
            <x-input-error :messages="$errors->get('catatan')" class="mt-1" />
        </div>

        <div>
            <x-input-label :value="__('Upload Tugas')" />
            <label for="file" id="upload-box" class="mt-1 flex flex-col items-center justify-center gap-1 border-2 border-dashed border-slate-200 rounded-xl px-4 py-8 text-center cursor-pointer hover:border-primary-500 hover:bg-primary-50/50 transition">
                <span id="upload-empty">
                    <i data-lucide="upload" class="w-8 h-8 mx-auto text-slate-400"></i>
                    <span class="mt-2 block text-sm font-semibold text-slate-700">Klik untuk memilih foto</span>
                    <span class="block text-xs text-slate-500">JPG, JPEG, PNG, WEBP · Maks. 10 MB</span>
                </span>
                <span id="upload-selected" class="hidden">
                    <i data-lucide="circle-check" class="w-8 h-8 mx-auto text-green-600"></i>
                    <span id="upload-name" class="mt-2 block text-sm font-semibold text-slate-700 break-all"></span>
                    <span id="upload-size" class="block text-xs text-slate-500"></span>
                    <span class="mt-1 block text-xs font-semibold text-green-600">Siap dikumpulkan</span>
                </span>
            </label>
            <input id="file" name="file" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only" required>
            <x-input-error :messages="$errors->get('file')" class="mt-1" />
        </div>

        <x-primary-button id="submit-btn" class="w-full">
            Kumpulkan Tugas
        </x-primary-button>
        <div id="uploading" class="hidden items-center justify-center gap-2 text-sm text-slate-500">
            <i data-lucide="loader-circle" class="w-4 h-4 animate-spin"></i>
            Mengunggah...
        </div>
    </form>

    <script>
    (function () {
        const input = document.getElementById('file');
        const empty = document.getElementById('upload-empty');
        const selected = document.getElementById('upload-selected');
        const name = document.getElementById('upload-name');
        const size = document.getElementById('upload-size');
        const form = document.getElementById('submit-form');
        const btn = document.getElementById('submit-btn');
        const uploading = document.getElementById('uploading');

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) {
                empty.classList.remove('hidden');
                selected.classList.add('hidden');
                return;
            }
            empty.classList.add('hidden');
            selected.classList.remove('hidden');
            name.textContent = file.name;
            const mb = file.size / 1024 / 1024;
            size.textContent = mb >= 1 ? mb.toFixed(1) + ' MB' : Math.max(1, Math.round(file.size / 1024)) + ' KB';
        });

        form.addEventListener('submit', () => {
            btn.disabled = true;
            btn.classList.add('opacity-50');
            uploading.classList.remove('hidden');
            uploading.classList.add('flex');
        });
    })();
    </script>
</x-student-layout>
