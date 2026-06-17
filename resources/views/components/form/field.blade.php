@props([
    'label',
    'name',
    'labelClass' => 'text-white/70',
])

<div>
    <label for="{{ $name }}" class="mb-2 block text-xs font-semibold tracking-wide {{ $labelClass }}">
        {{ $label }}
    </label>

    {{ $slot }}

    @error($name)
        <p class="mt-2 text-xs font-medium text-danger">{{ $message }}</p>
    @enderror
</div>
