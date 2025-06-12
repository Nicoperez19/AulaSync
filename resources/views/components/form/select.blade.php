@props(['name', 'id' => null, 'options' => [], 'selected' => null])

<select name="{{ $name }}" id="{{ $id ?? $name }}" {{ $attributes->merge(['class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white']) }}>
    <option value="" disabled selected>Seleccione una opción</option>
    @foreach($options as $value => $label)
        <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>