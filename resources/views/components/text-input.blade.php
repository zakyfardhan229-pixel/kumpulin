@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-200 focus:border-primary-500 focus:ring-primary-500 rounded-[10px] shadow-sm text-sm']) }}>
