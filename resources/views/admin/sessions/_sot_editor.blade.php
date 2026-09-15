{{-- Expects: $questions (array of normalized question rows for repopulation), $validationTypes --}}
<div id="sot-editor" class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="font-semibold text-slate-900">Source of Truth</h3>
        <button type="button" id="sot-add" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary-600 hover:text-primary-700">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Tambah Soal
        </button>
    </div>

    <div id="sot-questions" class="space-y-4">
        @foreach ($questions as $i => $q)
            <fieldset class="sot-question border border-slate-200 rounded-xl p-4 space-y-3" data-index="{{ $i }}">
                <div class="flex items-center justify-between">
                    <legend class="font-semibold text-sm text-slate-700">Soal <span class="sot-number">{{ $i + 1 }}</span></legend>
                    <button type="button" class="sot-remove text-xs font-medium text-slate-400 hover:text-red-600 inline-flex items-center gap-1">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Hapus
                    </button>
                </div>
                <div>
                    <x-input-label :value="__('Pertanyaan')" />
                    <x-textarea-input class="mt-1 block w-full" rows="2" :name="'questions['.$i.'][question]'" required>{{ old('questions.'.$i.'.question', $q['question'] ?? '') }}</x-textarea-input>
                    <x-input-error :messages="$errors->get('questions.'.$i.'.question')" class="mt-1" />
                </div>
                <div>
                    <x-input-label :value="__('Jawaban / Konsep yang Diharapkan')" />
                    <x-textarea-input class="mt-1 block w-full" rows="3" :name="'questions['.$i.'][expected]'" required>{{ old('questions.'.$i.'.expected', $q['expected'] ?? '') }}</x-textarea-input>
                    <x-input-error :messages="$errors->get('questions.'.$i.'.expected')" class="mt-1" />
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <x-input-label :value="__('Tipe Validasi')" />
                        <select name="questions[{{ $i }}][validation_type]" class="mt-1 block w-full border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            @foreach ($validationTypes as $type)
                                <option value="{{ $type }}" @selected(old('questions.'.$i.'.validation_type', $q['validation_type'] ?? 'semantic') === $type)>{{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('questions.'.$i.'.validation_type')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label :value="__('Konsep Wajib (pisahkan koma)')" />
                        <x-text-input class="mt-1 block w-full" type="text" :name="'questions['.$i.'][required_concepts]'" :value="old('questions.'.$i.'.required_concepts', isset($q['required_concepts']) && is_array($q['required_concepts']) ? implode(', ', $q['required_concepts']) : ($q['required_concepts'] ?? ''))" placeholder="langkah sistematis, instruksi" />
                        <x-input-error :messages="$errors->get('questions.'.$i.'.required_concepts')" class="mt-1" />
                    </div>
                </div>
            </fieldset>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('questions')" class="mt-1" />

    <template id="sot-template">
        <fieldset class="sot-question border border-slate-200 rounded-xl p-4 space-y-3" data-index="__INDEX__">
            <div class="flex items-center justify-between">
                <legend class="font-semibold text-sm text-slate-700">Soal <span class="sot-number"></span></legend>
                <button type="button" class="sot-remove text-xs font-medium text-slate-400 hover:text-red-600 inline-flex items-center gap-1">
                    Hapus
                </button>
            </div>
            <div>
                <label class="block font-medium text-sm text-slate-700">Pertanyaan</label>
                <textarea class="mt-1 block w-full border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" rows="2" name="questions[__INDEX__][question]" required></textarea>
            </div>
            <div>
                <label class="block font-medium text-sm text-slate-700">Jawaban / Konsep yang Diharapkan</label>
                <textarea class="mt-1 block w-full border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" rows="3" name="questions[__INDEX__][expected]" required></textarea>
            </div>
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-medium text-sm text-slate-700">Tipe Validasi</label>
                    <select name="questions[__INDEX__][validation_type]" class="mt-1 block w-full border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                        @foreach ($validationTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-sm text-slate-700">Konsep Wajib (pisahkan koma)</label>
                    <input class="mt-1 block w-full border-slate-200 rounded-[10px] shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" type="text" name="questions[__INDEX__][required_concepts]" placeholder="langkah sistematis, instruksi" />
                </div>
            </div>
        </fieldset>
    </template>
</div>

<script>
(function () {
    const list = document.getElementById('sot-questions');
    const tpl = document.getElementById('sot-template').innerHTML;
    let nextIndex = {{ count($questions) }};

    function renumber() {
        list.querySelectorAll('.sot-question').forEach((el, i) => {
            el.querySelector('.sot-number').textContent = i + 1;
        });
    }

    document.getElementById('sot-add').addEventListener('click', () => {
        const html = tpl.replaceAll('__INDEX__', nextIndex++);
        list.insertAdjacentHTML('beforeend', html);
        renumber();
    });

    list.addEventListener('click', (e) => {
        const btn = e.target.closest('.sot-remove');
        if (!btn) return;
        if (list.querySelectorAll('.sot-question').length <= 1) return;
        btn.closest('.sot-question').remove();
        renumber();
    });

    renumber();
})();
</script>
